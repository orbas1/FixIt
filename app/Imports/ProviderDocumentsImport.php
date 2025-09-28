<?php

namespace App\Imports;

use App\Enums\UserTypeEnum;
use App\Models\UserDocument;
use App\Enums\RoleEnum;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\ExceptionHandler;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Company;

class ProviderDocumentsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    private $ProviderDocuments = [];

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'document_id' => 'required|exists:documents,id',
            'identity_no' => 'required|string',
            'status' => 'required',
            'image' => 'required',
        ];
    }

    public function customValidationMessages()
    {
        return [
          
        ];
    }

    /**
     * @param \Throwable $e
     */
    public function onError(\Throwable $e)
    {
        throw new ExceptionHandler($e->getMessage(), 422);
    }

    public function getImportedDocuments()
    {

        return $this->ProviderDocuments;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $document = new UserDocument([
            'user_id' => $row['user_id'],
            'document_id' => $row['document_id'],
            'notes' => $row['notes'],
            'identity_no' => $row['identity_no'],
            'status' => $row['status'],


        ]);

        if (isset($row['document_image'])) {
            $media = $document->addMediaFromUrl($row['document_image'])->toMediaCollection('provider_documents');
        }
        $document->save();

        $this->ProviderDocuments[] = [
            'user_id' => $document?->id,
            'document_id'  => $document?->name,
            'notes' => $document?->email,
            'identity_no' => $document?->code,
            'status' => $document?->phone,
        ];

        return $document;
    }
}
