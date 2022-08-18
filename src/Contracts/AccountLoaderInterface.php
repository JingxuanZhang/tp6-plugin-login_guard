<?php


namespace Moonpie\Tp6Plugin\LoginGuard\Contracts;


use think\Request;

interface AccountLoaderInterface
{
    /**
     * 加载需要保护的用户信息
     * @param Request $request
     * @return AccountWithStateInterface
     */
    public function loadAccountFromRequest(Request $request);

    /**
     * 判断帐号认证信息是否有效
     * @param AccountWithStateInterface $account
     * @param Request $request
     * @return boolean
     */
    public function isAccountCreditVerified(Request $request, AccountWithStateInterface $account = null);

    /**
     * 获取该请求中的用户类型
     * @param Request $request
     * @return mixed
     */
    public function loadAccountTypeFromRequest(Request $request);
}