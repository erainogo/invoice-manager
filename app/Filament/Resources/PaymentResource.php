<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('customer_name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('customer_email')
                    ->email()
                    ->required(),

                Forms\Components\TextInput::make('reference_number')
                    ->required()
                    ->maxLength(100),

                Forms\Components\DatePicker::make('payment_date')
                    ->required(),

                Forms\Components\TextInput::make('original_amount')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('original_currency')
                    ->required()
                    ->maxLength(3),

                Forms\Components\TextInput::make('usd_amount')
                    ->numeric()
                    ->required(),

                Forms\Components\Select::make('status')
                    ->options([
                        'success' => 'processed',
                        'pending' => 'unprocessed',
                        'danger' => 'failed',
                    ])
                    ->required(),

                Forms\Components\Textarea::make('error_message')
                    ->maxLength(500),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->searchable(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer_email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Reference No.')
                    ->searchable(),

                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Payment Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('original_amount')
                    ->label('Original')
                    ->money(fn ($record) => $record->original_currency)
                    ->sortable(),

                Tables\Columns\TextColumn::make('usd_amount')
                    ->label('USD')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'processed',
                        'pending' => 'unprocessed',
                        'danger' => 'failed',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('error_message')
                    ->label('Error')
                    ->toggleable()
                    ->limit(50),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'processed' => 'processed',
                        'unprocessed' => 'unprocessed',
                        'failed' => 'failed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
            'view' => Pages\ViewPayment::route('/{record}'),
        ];
    }
}
