<?php


namespace Moonpie\Tp6Plugin\LoginGuard\Contracts;


interface AccountWithStateInterface
{
    /**
     * @return boolean 是否可冻结
     */
    public function canLockAccount();

    /**
     * @return boolean 是否可以解冻账户
     */
    public function canUnlockAccount();

    public function markAsAccountFrozen();
    public function markAsAccountValid($clearFailures = true);
    public function markAsAccountTempFrozen();

    /**
     * @return boolean 是否是永久封禁账户
     */
    public function isForeverLockAccount();

    /**
     * @return boolean 是否是可用账户
     */
    public function isValidAccount();

    /**
     * @return boolean 是否是临时封禁账户
     */
    public function isTempLockAccount();


    /**
     * @return integer 账户类型唯一标识
     */
    public function getAccountType();
    public function getAccountId();
}