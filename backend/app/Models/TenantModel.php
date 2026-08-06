<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\CompanyScope;
use App\Services\CurrentCompany;

abstract class TenantModel extends BaseModel
{
    protected static function boot(): void
    {
        parent::booted();

        static::addGlobalScope(
            app(CompanyScope::class)
        );


        static::creating(function (TenantModel $model): void {

            if ($model->company_id) {
                return;
            }

            $companyId = app(CurrentCompany::class)->id();

            if ($companyId !== null) {
                $model->company_id = $companyId;
            }
        });
    }
}
