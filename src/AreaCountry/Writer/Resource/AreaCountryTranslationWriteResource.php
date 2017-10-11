<?php declare(strict_types=1);

namespace Shopware\AreaCountry\Writer\Resource;

use Shopware\AreaCountry\Event\AreaCountryTranslationWrittenEvent;
use Shopware\Context\Struct\TranslationContext;
use Shopware\Framework\Write\Field\FkField;
use Shopware\Framework\Write\Field\ReferenceField;
use Shopware\Framework\Write\Field\StringField;
use Shopware\Framework\Write\Flag\Required;
use Shopware\Framework\Write\WriteResource;
use Shopware\Shop\Writer\Resource\ShopWriteResource;

class AreaCountryTranslationWriteResource extends WriteResource
{
    protected const NAME_FIELD = 'name';

    public function __construct()
    {
        parent::__construct('area_country_translation');

        $this->fields[self::NAME_FIELD] = (new StringField('name'))->setFlags(new Required());
        $this->fields['areaCountry'] = new ReferenceField('areaCountryUuid', 'uuid', AreaCountryWriteResource::class);
        $this->primaryKeyFields['areaCountryUuid'] = (new FkField('area_country_uuid', AreaCountryWriteResource::class, 'uuid'))->setFlags(new Required());
        $this->fields['language'] = new ReferenceField('languageUuid', 'uuid', ShopWriteResource::class);
        $this->primaryKeyFields['languageUuid'] = (new FkField('language_uuid', ShopWriteResource::class, 'uuid'))->setFlags(new Required());
    }

    public function getWriteOrder(): array
    {
        return [
            AreaCountryWriteResource::class,
            ShopWriteResource::class,
            self::class,
        ];
    }

    public static function createWrittenEvent(array $updates, TranslationContext $context, array $rawData = [], array $errors = []): AreaCountryTranslationWrittenEvent
    {
        $event = new AreaCountryTranslationWrittenEvent($updates[self::class] ?? [], $context, $rawData, $errors);

        unset($updates[self::class]);

        if (!empty($updates[AreaCountryWriteResource::class])) {
            $event->addEvent(AreaCountryWriteResource::createWrittenEvent($updates, $context));
        }
        if (!empty($updates[ShopWriteResource::class])) {
            $event->addEvent(ShopWriteResource::createWrittenEvent($updates, $context));
        }
        if (!empty($updates[self::class])) {
            $event->addEvent(self::createWrittenEvent($updates, $context));
        }

        return $event;
    }
}