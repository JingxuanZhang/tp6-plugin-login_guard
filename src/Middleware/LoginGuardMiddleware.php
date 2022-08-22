<?php


namespace Moonpie\Tp6Plugin\LoginGuard\Middleware;


use Moonpie\Tp6Plugin\LoginGuard\Contracts\AccountLoaderInterface;
use Moonpie\Tp6Plugin\LoginGuard\Contracts\GuardInterface;
use think\Request;

class LoginGuardMiddleware
{
    /** @var  AccountLoaderInterface $loader */
    protected $loader;
    /** @var GuardInterface */
    protected $guard;
    public function __construct(
        AccountLoaderInterface $loader,
        GuardInterface $guard
    )
    {
        $this->loader = $loader;
        $this->guard = $guard;
    }
    public function handle(Request $request, callable $next)
    {
        if (!$this->isValidRequest($request)) {
            return $next($request);
        }
        $account = $this->loader->loadAccountFromRequest($request);
        //没有找到用户
        if (is_null($account)) {
            $vote = $this->guard->voteSceneNoAccount($this->loader->loadAccountTypeFromRequest($request));
            if($vote === GuardInterface::VOTE_REFUSED) {
                $this->guard->logFailure($request, $account);
                return $this->formatFailReason($request, $this->guard->getFailReason());
            }
        }else {
            //有用户
            //这里需要投票,用户登录成功,但还受限制的时候怎么办
            $vote = $this->guard->voteSceneAccountUnderLimit($account);
            if ($vote === GuardInterface::VOTE_REFUSED) {
                $this->guard->logFailure($request, $account);
                return $this->formatFailReason($request, $this->guard->getFailReason());
            }
            if (!$this->loader->isAccountCreditVerified($request, $account)) {
                $this->guard->logFailure($request, $account);
                return $this->formatFailReason($request, $this->guard->getFailReason());
            } else {
                $this->guard->clearFailures($account, $request);
            }
        }
        return $next($request);
    }
    protected function isValidRequest(Request $request)
    {
        return $request->isPost();
    }
    protected function formatFailReason(Request $request, $reason)
    {
        $data = ['code'    => 0,
                 'msg' => $reason,
                 'data'    => [],'url' => '',
        ];

        return json($data);
    }
}