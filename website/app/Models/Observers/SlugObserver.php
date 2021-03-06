<?php

namespace App\Models\Observers;

use Config;

class SlugObserver
{
    public static $SET_SLUGS_ON_UPDATE = true;

    public function created($model)
    {
        if ($model->slugAttributes) {
            $languages = array_keys(Config::get('app.locales', []));
            foreach ($languages as $locale) {
                $model->setSlug($model->getSlugParams($locale));
            }
        }
    }

    public function updated($model)
    {
        if (self::$SET_SLUGS_ON_UPDATE && $model->slugAttributes) {
            $languages = array_keys(Config::get('app.locales', []));
            foreach ($languages as $locale) {
                if ($model->isDirty($model->slugAttributes) ||
                    (property_exists($model, 'translatedAttributes')  && !empty($model->getTranslatedAttributes()) &&
                        $model->getTranslation($locale) && $model->getTranslation($locale)->isDirty($model->slugAttributes)
                    )
                ) {
                    $model->setSlug($model->getSlugParams($locale));
                }
            }
        }
    }
}
