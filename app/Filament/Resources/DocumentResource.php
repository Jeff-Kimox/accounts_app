<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentItem;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-document';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Select::make('document_type')
                    ->options([
                        'Invoice' => 'Invoice',
                        'Proforma Invoice' => 'Proforma Invoice',
                        'Receipt' => 'Receipt',
                        'Credit Note' => 'Credit Note',
                        'Debit Note' => 'Debit Note',
                        'Purchase Order' => 'Purchase Order',
                        'Delivery Note' => 'Delivery Note',
                        'Goods Received Note' => 'Goods Received Note',
                        'Quotation/Estimate' => 'Quotation/Estimate',
                        'Tax Invoice' => 'Tax Invoice',
                        'Statement of Account' => 'Statement of Account',
                    ])
                    ->required(),

                Select::make('customer_id')
                    ->label('Customer')
                    ->options(Customer::all()->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                        TextInput::make('email')->email(),
                        TextInput::make('phone'),
                    ])
                    ->createOptionUsing(fn(array $data) => Customer::create($data)),

                Repeater::make('items')
                    ->relationship('items')
                    ->schema([
                        TextInput::make('item_name')->required(),

                        TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn(callable $set, callable $get) =>
                                $set('subtotal', ($get('quantity') ?? 0) * ($get('price') ?? 0))
                            ),

                        TextInput::make('price')
                            ->numeric()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn(callable $set, callable $get) =>
                                $set('subtotal', ($get('quantity') ?? 0) * ($get('price') ?? 0))
                            ),

                        TextInput::make('subtotal')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(4)
                    ->live()
                    ->afterStateUpdated(fn(callable $set, callable $get) =>
                        $set('total_amount', collect($get('items'))->sum(fn ($item) => $item['subtotal'] ?? 0))
                    ),

                TextInput::make('total_amount')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false)
                    ->afterStateHydrated(fn(callable $set, $record) => 
                        $set('total_amount', $record?->items->sum('subtotal') ?? 0)
                    ),
            ]);
    }


    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('document_type')->sortable(),
                TextColumn::make('customer.name')->sortable(),
                TextColumn::make('total_amount')->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('print')
                    ->label('Print')
                    ->url(fn($record) => route('documents.print', $record)),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }
}
