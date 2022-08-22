<?php


namespace Moonpie\Tp6Plugin\LoginGuard\Models;


use Moonpie\Tp6Plugin\LoginGuard\Contracts\AccountWithStateInterface;
use Moonpie\Tp6Plugin\LoginGuard\Contracts\GuardInterface;
use Moonpie\Tp6Plugin\LoginGuard\Contracts\LimitConfigInterface;
use think\Model;
use think\Request;

class IpBlockRecordModel extends Model implements GuardInterface
{
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $name       = 'ip_block_record';
    protected $voteAccountType;
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

    public function logFailure(
        Request $request,
        AccountWithStateInterface $user = null
    ) {
        if (is_null($user)) {
            $this->failReason = '用户名或密码错误';
        } else {
            $this->failReason = '用户登录信息认证失败';
        }
        $ip = $request->ip();
        $this->startTrans();
        try {
            $time        = time();
            $type        = is_null(
                $user
            ) ? $this->voteAccountType : $user->getAccountType();
            $record      = static::where('user_type', $type)
                                 ->where('ip', $ip)
                                 ->withoutField(
                                     ['update_time', 'create_time']
                                 )
                                 ->find();
            $temp_config = $this->getLimitConfig()->loadConfig(
                LimitConfigInterface::KEY_IP_TEMP_LOCK,
                null
            );
            if ($record) {

                $close_time = $record->getData('close_time');
                $fail_times = $record->getData('fail_times');
                $over_times = $record->getData('over_times');
                $free_time  = $record->getData('free_time');
                $lock_times = $record->getData('lock_times');
                if ($fail_times >= $over_times) {
                    $diff = $free_time - $time;
                    if ($diff > 0) {
                        $this->rollback();
                        $this->failReason = 'IP处于临时封禁状态,请在'.format_human_duration(
                                $diff
                            ).'后重试';

                        return false;
                    }
                }
                //如果超过检测的时间
                if ($time >= $close_time) {
                    $fail_times = 1;
                    $lock_times += $fail_times >= $temp_config['times'] ? 1 : 0;
                    $close_time = $time + 60 * $temp_config['minutes'];
                    $free_time  = $time + 60 * $temp_config['lock_minutes'];
                    $save       = [
                        'start_time' => $time,
                        'lock_times' => $lock_times,
                        'fail_times' => $fail_times,
                        'free_time'  => $free_time,
                        'over_times' => $temp_config['times'],
                        'close_time' => $close_time,
                    ];
                } else {

                    //还在范围空间
                    $free_time  = $time + 60 * $temp_config['lock_minutes'];
                    $lock_times = $record->getData('lock_times');
                    $fail_times += 1;
                    $lock_times += ($fail_times >= $over_times ? 1 : 0);
                    $save       = [
                        'fail_times' => $fail_times,
                        'free_time'  => $free_time,
                        'lock_times' => $lock_times,
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
                    'ip'         => $ip,
                    'start_time' => $time,
                    'fail_times' => $fail_times,
                    'close_time' => $close_time,
                    'over_times' => $temp_config['times'],
                    'free_time'  => $free_time,
                    'lock_times' => $lock_times,
                ];

            }
            $record->save($save);
            $over_times = $record->getData('over_times');
            $fail_times = $record->getData('fail_times');
            $this->commit();
            if ($fail_times >= $over_times) {
                $this->failReason = 'IP登录失败已超过最大次数,系统临时封禁';

                return true;
            }

            return $record;
        } catch (\Exception $e) {
            $this->rollback();
            $this->failReason = 'IP异常失败记录异常'.$e->getMessage();

            return false;
        }
    }

    public function clearFailures(
        AccountWithStateInterface $user,
        Request $request = null
    ) {
        if ($user->isForeverLockAccount()) {
            $this->failReason = '抱歉,永久封禁账户不得删除登录失败记录';

            return false;
        }
        $type   = $user->getAccountType();
        $record = static::where('user_type', $type)
                        ->where('ip', $request->ip())
                        ->withoutField(['update_time', 'create_time'])
                        ->find();
        if ($record) {
            $record->delete();
        }

        return true;
    }

    public function getFailReason()
    {
        return $this->failReason;
    }

    public function voteSceneNoAccount($accountType)
    {
        $this->voteAccountType = $accountType;

        return static::VOTE_REFUSED;
    }

    public function voteSceneAccountUnderLimit(
        AccountWithStateInterface $account, Request $request
    ) {
        $time        = time();
        $type        = $account->getAccountType();
        $record      = static::where('user_type', $type)
                             ->where('ip', $request->ip)
                             ->withoutField(
                                 ['update_time', 'create_time']
                             )
                             ->find();
        if (!$record) {
            return static::VOTE_ACCEPT;
        }
        if ($record->getData('close_time') >= $time) {
            return static::VOTE_ACCEPT;
        }
        return static::VOTE_GIVE_UP;

    }

}