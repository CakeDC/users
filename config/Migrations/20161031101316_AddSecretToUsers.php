<?php
use Migrations\AbstractMigration;

class AddSecretToUsers extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table = $this->table('users');
        $table->addColumn('secret', 'string', [
            'after' => 'activation_date',
            'default' => null,
            'limit' => 255,
            'null' => true,
        ]);
        $table->addColumn('secret_verified', 'boolean', [
            'after' => 'secret',
            'default' => null,
            'null' => true,
        ]);
        $table->update();
    }
}
