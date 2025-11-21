<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Models\Order;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Number;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ShoppingBag;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Group::make()->schema([
                    Section::make('Order Information')
                        ->schema([
                            Select::make('user_id')
                                ->label('Customer')
                                ->relationship('user', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),

                            Select::make('payment_method')
                                ->options([
                                    'credit_card' => 'Credit Card',
                                    'paypal' => 'PayPal',
                                    'bank_transfer' => 'Bank Transfer',
                                    'cod' => 'Cash on Delivery',
                                ])
                                ->required(),

                            Select::make('payment_status')
                                ->options([
                                    'pending' => 'Pending',
                                    'paid' => 'Paid',
                                    'failed' => 'Failed',
                                ])
                                ->default('pending')
                                ->required(),

                            ToggleButtons::make('status')
                                ->inline()
                                ->default('new')
                                ->required()
                                ->options([
                                    'new' => 'New',
                                    'processing' => 'Processing',
                                    'shipped' => 'Shipped',
                                    'delivered' => 'Delivered',
                                    'canceled' => 'Canceled',
                                ])
                                ->colors([
                                    'new' => 'info',
                                    'processing' => 'warning',
                                    'shipped' => 'success',
                                    'delivered' => 'success',
                                    'canceled' => 'danger',
                                ])
                                ->icons([
                                    'new' => 'heroicon-m-sparkles',
                                    'processing' => 'heroicon-m-arrow-path',
                                    'shipped' => 'heroicon-m-truck',
                                    'delivered' => 'heroicon-m-check-badge',
                                    'canceled' => 'heroicon-m-x-circle',
                                ]),

                            Select::make('currency')
                                ->options([
                                    'INR' => 'INR',
                                    'USD' => 'USD',
                                    'EUR' => 'EUR',
                                    'GBP' => 'GBP',
                                ])
                                ->default('USD')
                                ->required(),

                            Select::make('shipping_method')
                                ->options([
                                    'fedex' => 'FedEx',
                                    'ups' => 'UPS',
                                    'amazon' => 'Amazon',
                                ]),

                            Textarea::make('notes')
                                ->columnSpanFull(),
                        ])->columns(2),

                    Section::make('Order Items')
                        ->schema([
                            Repeater::make('items')
                                ->relationship()
                                ->schema([
                                    Select::make('product_id')
                                        ->label('Product')
                                        ->relationship('product', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->distinct()
                                        ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                        ->columnSpan(4)
                                        ->reactive()
                                        ->afterStateHydrated(function ($state, $set) {
                                            if ($state) {
                                                $product = Product::find($state);
                                                $price = $product?->price ?? 0;
                                                $set('unit_amount', $price);
                                                $set('total_amount', $price);
                                            }
                                        })
                                        ->afterStateUpdated(function ($state, $set, $get) {
                                            $product = Product::find($state);
                                            $price = $product?->price ?? 0;
                                            $set('unit_amount', $price);
                                            $set('total_amount', $price * ($get('quantity') ?? 1));
                                        }),

                                    TextInput::make('quantity')
                                        ->numeric()
                                        ->required()
                                        ->minValue(1)
                                        ->default(1)
                                        ->reactive()
                                        ->afterStateHydrated(function ($state, $set, $get) {
                                            $set('total_amount', ($state ?? 1) * ($get('unit_amount') ?? 0));
                                        })
                                        ->afterStateUpdated(function ($state, $get, $set) {
                                            $set('total_amount', $state * ($get('unit_amount') ?? 0));
                                        })
                                        ->columnSpan(2),

                                    TextInput::make('unit_amount')
                                        ->numeric()
                                        ->required()
                                        ->disabled()
                                        ->dehydrated()
                                        ->columnSpan(3),

                                    TextInput::make('total_amount')
                                        ->numeric()
                                        ->required()
                                        ->dehydrated()
                                        ->columnSpan(3),
                                ])
                                ->columns(12)
                                ->defaultItems(1),

                            Placeholder::make('grand_total')
                                ->label('Grand Total')
                                ->content(fn($get) => Number::currency(
                                    collect($get('items') ?? [])
                                        ->sum(fn($i) => $i['total_amount'] ?? 0),
                                    $get('currency') ?? 'USD'
                                )),

                            Hidden::make('grand_total')
                                ->dehydrated()
                                ->default(0)
                                ->dehydrateStateUsing(fn($get) => collect($get('items') ?? [])
                                    ->sum(fn($i) => $i['total_amount'] ?? 0)),
                        ])
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->formatStateUsing(fn($record) => Number::currency(
                        $record->items->sum(fn($i) => $i->total_amount ?? 0),
                        $record->currency ?? 'USD'
                    ))
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payment_status')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('currency')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('shipping_method')
                    ->searchable()
                    ->sortable(),

                SelectColumn::make('status')
                    ->options([
                        'new' => 'New',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'canceled' => 'Canceled',
                    ])
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault:true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault:true),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() > 10 ? 'success' : 'danger';
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'view' => ViewOrder::route('/{record}'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}
