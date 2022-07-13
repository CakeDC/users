<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddCode2fFields extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $this->table('users')
            ->addColumn('phone', 'string', [
                'null' => true,
                'default' => null,
                'length' => 256
            ])
            ->addColumn('phone_verified', 'datetime', [
                'null' => true,
                'default' => null,
            ])
            ->update();
        $this->table('otp_codes')
            ->addColumn('user_id', 'uuid', [
                'null' => false,
            ])
            ->addColumn('code', 'string', [
                'length' => 255,
                'null' => false,
                'default' => null
            ])
            ->addColumn('tries', 'integer', [
                'null' => false,
                'default' => 0
            ])
            ->addColumn('validated', 'datetime', [
                'null' => true,
                'default' => null,
            ])
            ->addColumn('created', 'datetime', [
                'null' => false,
                'default' => null,
            ])
            ->addForeignKey('user_id', 'users', 'id', array('delete' => 'CASCADE', 'update' => 'CASCADE'))
            ->create();
    }
}
