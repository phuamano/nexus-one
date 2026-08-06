<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Services\CurrentCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Spatie\Permission\DefaultTeamResolver;

class CompanyScope implements Scope
{
    public function __construct(
        private readonly CurrentCompany $currentCompany
    ){}

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $companyId = $this->currentCompany->id();

        if ($companyId === null) { return; }

        $builder->where($model->getTable() . '.company_id', $companyId);
    }

}
