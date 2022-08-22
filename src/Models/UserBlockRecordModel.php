<?php


namespace Moonpie\Tp6Plugin\LoginGuard\Models;


use Moonpie\Tp6Plugin\LoginGuard\Contracts\AccountWithStateInterface;
use Moonpie\Tp6Plugin\LoginGuard\Contracts\GuardInterface;
use Moonpie\Tp6Plugin\LoginGuard\Contracts\LimitConfigInterface;
use think\Model;
use think\Request;

class UserBlockRecordModel extends Model implements GuardInterface
{
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $name = 'user_block_record';
    protected $failReason;
    /** @var LimitConfigInterface $limitConfig */
    protected $limitConfig;
    public function getLimitConfig()
    {
        return $this->limitConfig;
    }
    public function setLimitConfig(LimitConfigInterface $config)
    {
        $this->limitConfig = $config;
        return $this;
    }
    public function logFailure(Request $request, AccountWithStateInterface $user = null)
    {
        if (is_null($user)) {
            $this->failReason = '用户名或密码错误';
        }else {
            $this->failReason = '登录信息认证失败';
        }
        $this->startTrans();
        try {
            $time          = time();
            $type          = $user->getAccountType();
            $record        = static::where('user_type', $type)
                                   ->where('user_id', $user->getAccountId())
                                   ->withoutField(
                                       ['update_time', 'create_time']
                                   )
                                   ->find();
            $forever_times = $this->getLimitConfig()->loadConfig(
                LimitConfigInterface::KEY_ACCOUNT_FOREVER_LOCK,
                'times',
                3
            );
            $temp_config   = $this->getLimitConfig()->loadConfig(
                LimitConfigInterface::KEY_ACCOUNT_TEMP_LOCK,
                null
            );
            if ($record) {

                $close_time = $record->getData('close_time');
                $fail_times = $record->getData('fail_times');
                $over_times = $record->getData('over_times');
                $free_time = $record->getData('free_time');
                $lock_times = $record->getData('lock_times');
                if ($user->isTempLockAccount()) {
                    $diff = $free_time - $time;
                    if ($diff > 0) {
                        $this->failReason = '账户处于临时封禁状态,请在' . format_human_duration($diff) . '后重试';
                        $this->rollback();
                        return false;
                    }
                }
                //如果超过检测的时间
                if ($time >= $close_time) {
                    $fail_times = 1;
                    $lock_times += $fail_times >= $temp_config['times'] ? 1 : 0;
                    $close_time = $time + 60 * $temp_config['minutes'];
                    $free_time  = $time + 60 * $temp_config['lock_minutes'];
                    $save = [
                        'start_time' => $time,'lock_times' => $lock_times,
                        'fail_times' => $fail_times,
                        'free_time' => $free_time,'over_times' => $temp_config['times'],
                        'close_time' => $close_time,
                    ];
                }else {

                    //还在范围空间
                    $free_time  = $time + 60 * $temp_config['lock_minutes'];
                    $lock_times = $record->getData('lock_times');
                    $fail_times+=1;
                    $lock_times += ($fail_times >= $over_times ? 1 : 0);
                    $save = [
                        'fail_times' => $fail_times,
                        'free_time' => $free_time, 'lock_times' => $lock_times,
                    ];
                }
            } else {
                $fail_times = 1;
                $record     = new static();
                $close_time = $time + 60 * $temp_config['minutes'];
                $free_time  = $time + 60 * $temp_config['lock_minutes'];
                $lock_times = $fail_times >= $temp_config['times'] ? 1 : 0;
                $save       = [
                    'user_type'  => $type,
                    'user_id'    => $user->getAccountId(),
                    'start_time' => $time,
                    'fail_times' => $fail_times,
                    'close_time' => $close_time,
                    'over_times' => $temp_config['times'],
                    'free_time'  => $free_time,
                    'lock_times' => $lock_times,
                ];

            }
            $record->save($save);
            $lock_times = $record->getData('lock_times');
            $over_times = $record->getData('over_times');
            $fail_times = $record->getData('fail_times');
            if ($lock_times >= $forever_times) {
                //永久封禁
                //$record->save('lock_times');
                $user->markAsAccountFrozen();
                $this->commit();
                $this->failReason = '用户已被永久冻结';
                return $record;
            }else if ($fail_times >= $over_times) {
                $user->markAsAccountTempFrozen();
                $this->failReason = '用户已被临时冻结';
            }else {
                $user->markAsAccountValid(false);
            }
            $this->commit();
            return $record;
        }catch (\Exception $e) {
            $this->rollback();
            $this->failReason = '登录失败记录异常' . $e->getMessage();
            return false;
        }
    }
    public function clearFailures(AccountWithStateInterface $user, Request $request = null)
    {
        if($user->isForeverLockAccount()) {
            $this->failReason = '抱歉,永久封禁账户不得删除登录失败记录';
            return false;
        }
        $type = $user->getAccountType();
        $record = static::where('user_type', $type)
            ->where('user_id', $user->getAccountId())
            ->withoutField(['update_time', 'create_time'])
            ->find();
        if ($record) {
            $record->delete();
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getFailReason()
    {
        return $this->failReason;
    }

    public function voteSceneNoAccount($accountType)
    {
        return static::VOTE_GIVE_UP;
    }

    public function voteSceneAccountUnderLimit(
        AccountWithStateInterface $account
    ) {
        return static::VOTE_ACCEPT;
    }

}