<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class ChangeAvatarColumnTypeInSocialAccounts extends BaseMigration
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
        $table = $this->table('social_accounts');
        $table->changeColumn('avatar', 'text', [
            'default' => null,
            'null' => true,
        ]);
        $table->update();
    }
}
