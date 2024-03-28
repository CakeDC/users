<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateFailedPasswordAttempts extends AbstractMigration
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
        $table = $this->table('failed_password_attempts', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'uuid', [
            'null' => false,
        ]);
        $table->addColumn('user_id', 'uuid', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('created', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addForeignKey('user_id', 'users', 'id', [
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ]);
        $table->create();
    }
}
