<?php
namespace verbb\auth\records;

use craft\db\ActiveRecord;

class Token extends ActiveRecord
{
    // Static Methods
    // =========================================================================

    public static function tableName(): string
    {
        return '{{%auth_oauth_tokens}}';
    }
}
