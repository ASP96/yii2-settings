<?php

namespace yii2mod\settings\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use Yii;
use yii2mod\enum\helpers\BooleanEnum;
use yii2mod\settings\models\enumerables\SettingType;

/**
 * This is the model class for table "Settings".
 *
 * @property integer $id
 * @property string $type
 * @property string $section
 * @property string $key
 * @property string $value
 * @property boolean $status
 * @property string $createdAt
 * @property string $updatedAt
 *
 */
class SettingModel extends ActiveRecord
{
    /**
     * @return string the table name
     */
    public static function tableName()
    {
        return '{{%Setting}}';
    }

    /**
     * @return array validation rules
     */
    public function rules()
    {
        return [
            [['section', 'key', 'value'], 'required'],
            [['section', 'key'], 'unique', 'targetAttribute' => ['section', 'key']],
            [['value', 'type'], 'string'],
            [['section', 'key'], 'string', 'max' => 255],
            [['status'], 'integer'],
            ['status', 'default', 'value' => BooleanEnum::YES],
            [['type'], 'safe'],
        ];
    }

    /**
     * @return array attribute labels (name => label)
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('yii2mod.settings', 'ID'),
            'type' => Yii::t('yii2mod.settings', 'Type'),
            'section' => Yii::t('yii2mod.settings', 'Section'),
            'key' => Yii::t('yii2mod.settings', 'Key'),
            'value' => Yii::t('yii2mod.settings', 'Value'),
            'status' => Yii::t('yii2mod.settings', 'Status'),
            'createdAt' => Yii::t('yii2mod.settings', 'Created date'),
            'updatedAt' => Yii::t('yii2mod.settings', 'Updated date'),
        ];
    }

    /**
     * Returns a list of behaviors that this component should behave as.
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'createdAt',
                'updatedAtAttribute' => 'updatedAt'
            ]
        ];
    }

    /**
     * Creates an [[ActiveQueryInterface]] instance for query purpose.
     *
     * @return SettingQuery
     */
    public static function find()
    {
        return new SettingQuery(get_called_class());
    }

    /**
     * This method is invoked after deleting a record.
     */
    public function afterDelete()
    {
        Yii::$app->settings->invalidateCache();
        parent::afterDelete();
    }

    /**
     * This method is called at the end of inserting or updating a record.
     *
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        Yii::$app->settings->invalidateCache();
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Return array of settings
     */
    public function getSettings()
    {
        $result = [];
        $settings = static::find()->select(['type', 'section', 'key', 'value'])->active()->asArray()->all();
        foreach ($settings as $setting) {
            $section = $setting['section'];
            $key = $setting['key'];
            $settingOptions = ['type' => $setting['type'], 'value' => $setting['value']];

            if (isset($result[$section][$key])) {
                ArrayHelper::merge($result[$section][$key], $settingOptions);
            } else {
                $result[$section][$key] = $settingOptions;
            }
        }

        return $result;
    }

    /**
     * Set setting
     *
     * @param $section
     * @param $key
     * @param $value
     * @param null $type
     * @return bool
     */
    public function setSetting($section, $key, $value, $type = null)
    {
        $settingTypes = SettingType::getConstantsByValue();
        $model = self::findOne(['section' => $section, 'key' => $key]);
        if (empty($model)) {
            $model = new self();
        }
        $model->section = $section;
        $model->key = $key;
        $model->value = strval($value);
        if ($type !== null && ArrayHelper::keyExists($type, $settingTypes)) {
            $model->type = $type;
        } else {
            $model->type = gettype($value);
        }
        return $model->save();
    }

    /**
     * Remove setting
     *
     * @param $section
     * @param $key
     * @return bool|int|null
     *
     * @throws \Exception
     */
    public function removeSetting($section, $key)
    {
        $model = self::findOne(['section' => $section, 'key' => $key]);
        if (!empty($model)) {
            return $model->delete();
        }
        return false;
    }

}