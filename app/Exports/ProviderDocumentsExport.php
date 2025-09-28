<?php

namespace App\Exports;


use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Models\UserDocument;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProviderDocumentsExport implements FromCollection,WithMapping,WithHeadings
{

    public function collection()
    {
        $providerDocuments = UserDocument::get();
        return $providerDocuments;
    }


    public function columns(): array
    {
        return [
            'user_id',
            'document_id',
            'notes',
            'identity_no',
            'status',
            'document_id',
            'document_image'
        ];
    }

    public function map($providerDocument): array
    {

        return [
            $providerDocument?->user_id,
            $providerDocument?->document_id,
            $providerDocument?->notes,
            $providerDocument?->identity_no,
            $providerDocument?->status,
            $providerDocument?->document_id,
            $providerDocument?->media?->first()?->original_url
        ];
    }

    public function headings(): array
    {
        return [
            'user_id',
            'document_id',
            'notes',
            'identity_no',
            'status',
            'document_id',
            'document_image'
        ];
    }

}
