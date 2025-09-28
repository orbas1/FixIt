<?php

namespace App\Repositories\API;

use App\Exceptions\ExceptionHandler;
use App\Models\Company;
use Exception;
use Prettus\Repository\Eloquent\BaseRepository;

class CompanyRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name' => 'like',
    ];

    public function model()
    {
        return Company::class;
    }

    public function getCompanyAddresses($request, $companyId)
    {
        try {
            $company = $this->model->findOrFail($companyId);
            $addresses = $company->serviceAvailabilities()->with('address');

            return $addresses->latest('created_at')->paginate($request->paginate);
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
