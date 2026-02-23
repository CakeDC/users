<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddLoginTokenToUsers extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('users');
        $table->addColumn('login_token', 'string', [
            'default' => null,
            'limit' => 32,
            'null' => true,
        ])->addColumn('login_token_date', 'datetime', [
            'default' => null,
            'null' => true,
        ])->addColumn('token_send_requested', 'boolean', [
            'default' => false,
            'null' => false,
        ])->addIndex('login_token');
        $table->update();
    }
}
