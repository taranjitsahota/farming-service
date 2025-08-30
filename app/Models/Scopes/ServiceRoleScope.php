<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ServiceRoleScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    /**
     * @method bool hasRole(string|array $roles)
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        if (!$user) {
            return;
        }

        if ($user->hasRole('admin')) {
            if ($model instanceof \App\Models\Substation) {
                $builder->where('id', $user->substation_id);
            }
            if ($model instanceof \App\Models\Equipment || $model instanceof \App\Models\Service || $model instanceof \App\Models\ServiceArea) {
                $builder->where('substation_id', $user->substation_id);
            }
        }

        if ($user->hasRole('farmer')) {
            $substationId = Request::get('substation_id');

            if ($model instanceof \App\Models\Substation) {
                $builder->where('id', $substationId);
            }
            if ($model instanceof \App\Models\Equipment || $model instanceof \App\Models\Service || $model instanceof \App\Models\ServiceArea) {
                $builder->where('substation_id', $substationId);
            }
            if (!$substationId) {
                throw new HttpException(422, 'The substation_id field is required.');
            }
        }
    }
}
