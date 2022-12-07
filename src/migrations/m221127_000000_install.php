<?php
namespace verbb\auth\migrations;

use craft\db\Migration;

class m221127_000000_install extends Migration
{
    // Public Methods
    // =========================================================================

    public function safeUp(): bool
    {
        if (!$this->db->tableExists('{{%auth_oauth_tokens}}')) {
            $this->createTable('{{%auth_oauth_tokens}}', [
                'id' => $this->primaryKey(),
                'ownerHandle' => $this->string()->notNull(),
                'providerType' => $this->string()->notNull(),
                'tokenType' => $this->string()->notNull(),
                'reference' => $this->string(),
                'accessToken' => $this->text(),
                'secret' => $this->text(),
                'expires' => $this->string(),
                'refreshToken' => $this->text(),
                'resourceOwnerId' => $this->string(),
                'values' => $this->text(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);
        }

        return true;
    }

    public function safeDown(): bool
    {
        return true;
    }
}
