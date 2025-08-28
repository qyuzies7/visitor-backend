<?php

namespace App\Exports;

use App\Models\CardTransaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CardTransactionExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;
    public function __construct($data)
    {
        $this->data = $data;
    }
    public function collection()
    {
        return $this->data;
    }
    public function headings(): array
    {
        return [
            'ID', 'Reference Number', 'Full Name', 'Transaction Type', 'Card Condition', 'Condition Notes', 'Handling Notes', 'Performed By', 'Processed At'
        ];
    }
    public function map($trx): array
    {
        return [
            $trx->id,
            $trx->visitorCard ? $trx->visitorCard->reference_number : '',
            $trx->visitorCard ? $trx->visitorCard->full_name : '',
            $trx->transaction_type,
            $trx->card_condition,
            $trx->condition_notes,
            $trx->handling_notes,
            $trx->performed_by_name_cached,
            $trx->processed_at,
        ];
    }
}
