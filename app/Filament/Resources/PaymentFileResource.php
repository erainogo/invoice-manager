<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentFileResource\Pages\CreatePaymentFile;
use App\Filament\Resources\PaymentFileResource\Pages\EditPaymentFile;
use App\Filament\Resources\PaymentFileResource\Pages\ListPaymentFiles;
use App\Models\PaymentFile;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class PaymentFileResource extends Resource
{
    protected static ?string $model = PaymentFile::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function table(Table|Tables\Table $table): Tables\Table
    {
        return $table->columns([
                Tables\Columns\TextColumn::make('file_name')->searchable(),

                Tables\Columns\TextColumn::make('status')->badge()->colors([
                    'success' => 'processed',
                    'pending' => 'processing',
                    'danger' => 'failed',
                ])->sortable(),

                Tables\Columns\TextColumn::make('uploaded_at')->dateTime(),

                Tables\Columns\TextColumn::make('processed_at')->dateTime(),
            ])
            ->filters([
            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'uploaded' => 'uploaded',
                    'processing' => 'processing',
                    'processed' => 'processed',
                    'failed' => 'failed',
                ]),
           ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaymentFiles::route('/'),
            'create' => CreatePaymentFile::route('/upload'),
        ];
    }
}

