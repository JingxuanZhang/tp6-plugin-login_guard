<?php


namespace Moonpie\Tp6Plugin\LoginGuard\Contracts;


use think\Request;

interface GuardInterface
{
    const VOTE_ACCEPT = 'accept';
    const VOTE_GIVE_UP = 'give-up';
    const VOTE_REFUSED = 'refused';
    /**
     * 记录失败
     * @param Request $request
     * @param AccountWithStateInterface $user
     * @return mixed
     */
    public function logFailure(Request $request, AccountWithStateInterface $user = null);

    /**
     * 清除失败记录
     * @param AccountWithStateInterface $user
     * @param Request|null $request
     * @return mixed
     */
    public function clearFailures(AccountWithStateInterface $user, Request $request = null);


    /**
     * @return string 返回失败原因
     */
    public function getFailReason();

    /**
     * 处理投票结果
     * GuardInterface::VOTE_GIVE_UP 该守卫不在意无用户
     * GuardInterface::VOTE_ACCEPT 该守卫允许无用户
     * GuardInterface::VOTE_REFUSED 该守卫不允许无用户
     * @param $accountType
     * @return string 投票结果
     */
    public function voteSceneNoAccount($accountType);
}