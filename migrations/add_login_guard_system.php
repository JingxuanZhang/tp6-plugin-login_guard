<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AddLoginGuardSystem extends Migrator
{
    protected $tableUserBlockRecord = 'user_block_record';
    protected $tableIpBlock = 'ip_block_record';
    public function up()
    {
        $this->createUserBlockRecordTable();
        $this->createIpBlockTable();
    }
    public function down()
    {
        $this->dropIpBlockTable();
        $this->dropUserBlockRecordTable();
    }
    //用户冻结部分
    protected function createUserBlockRecordTable()
    {
        if (!$this->hasTable($this->tableUserBlockRecord)) {
            $table = $this->table($this->tableUserBlockRecord, ['engine' => 'InnoDB', 'comment' => '用户临时冻结记录']);
            $table->addColumn(Column::boolean('user_type')->setComment('用户类型'))
            ->addColumn(Column::integer('user_id')->setComment('用户ID'))
                ->addColumn(Column::integer('start_time')->setComment('临时冻结开始时间'))
                ->addColumn(Column::integer('over_times')->setComment('阈值次数'))
                ->addColumn(Column::integer('fail_times')->setComment('失败次数'))
                ->addColumn(Column::integer('lock_times')->setComment('临时封禁次数'))
                ->addColumn(Column::integer('close_time')->setComment('规则失效时间'))
                ->addColumn(Column::integer('free_time')->setComment('临时解冻时间'))
                ->addColumn(Column::integer('create_time')->setComment('记录添加时间'))
                ->addColumn(Column::integer('update_time')->setComment('记录修改时间'))
                ->addIndex(['user_type', 'user_id'], ['name' => 'uniq_user', 'unique' => true])
                ->create();
        }
    }
    protected function dropUserBlockRecordTable()
    {
        if ($this->hasTable($this->tableUserBlockRecord)) {
            $this->dropTable($this->tableUserBlockRecord);
        }
    }
    //IP冻结部分
    protected function createIpBlockTable()
    {
        if (!$this->hasTable($this->tableIpBlock)) {
            $table = $this->table($this->tableIpBlock, ['engine' => 'InnoDB', 'comment' => 'IP临时冻结记录']);
            $table->addColumn(Column::boolean('user_type')->setComment('用户类型'))
                  ->addColumn(Column::string('ip')->setComment('临时冻结IP'))
                  ->addColumn(Column::integer('start_time')->setComment('临时冻结开始时间'))
                  ->addColumn(Column::integer('over_times')->setComment('阈值次数'))
                  ->addColumn(Column::integer('fail_times')->setComment('失败次数'))
                  ->addColumn(Column::integer('lock_times')->setComment('临时封禁次数'))
                  ->addColumn(Column::integer('close_time')->setComment('规则失效时间'))
                  ->addColumn(Column::integer('free_time')->setComment('临时解冻时间'))
                  ->addColumn(Column::integer('create_time')->setComment('记录添加时间'))
                  ->addColumn(Column::integer('update_time')->setComment('记录修改时间'))
                  ->addIndex(['user_type', 'ip'], ['name' => 'uniq_user_type_ip', 'unique' => true])
                  ->create();
        }
    }
    protected function dropIpBlockTable()
    {
        if ($this->hasTable($this->tableIpBlock)) {
            $this->dropTable($this->tableIpBlock);
        }
    }
}
