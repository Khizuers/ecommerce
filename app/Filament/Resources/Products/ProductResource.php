<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Squares2x2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Group::make()->schema([
                    Section::make('Product Information')
                    ->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function(String $operation, $state, $set ){
                                    if ($operation !== 'create') {
                                        return;
                                    }
                                    $set('slug', str()->slug($state));
                                }),
                                    
                            TextInput::make('slug')
                                ->required()
                                ->maxLength(255)
                                ->disabled()
                                ->dehydrated()
                                ->unique(Product::class, 'slug', ignoreRecord: true),
                                
                            MarkdownEditor::make('description')
                                ->columnSpanFull()
                                ->fileAttachmentsDirectory('products')
                    ])->columns(2),

                    Section::make('Images')->schema([ 
                            FileUpload::make('images')
                            ->multiple()
                            ->directory('products')
                            ->maxFiles(5)
                            ->reorderable()
                    ])
                    ])->columnSpan(2),

                    Group::make()->schema([
                        Section::make('Price')->schema([
                            TextInput::make('price')
                                ->required()
                                ->numeric()
                                ->prefix('USD')
                    ]),

                    Section::make('Associations')->schema([
                        Select::make('category_id')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->relationship('category', 'name'),

                        Select::make('brand_id')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->relationship('brand', 'name'),
                    ]),
                        Section::make('Status')->schema([
                            Toggle::make('in_stock')
                                ->required()
                                ->default(true),
                            
                            Toggle::make('is_active')
                                ->required()
                                ->default(true),

                            Toggle::make('is_featured')
                                ->required(),
                            
                            Toggle::make('on_sale')
                                ->required(),
                        ]),
                    ])->columnSpan(1),
                ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),

                TextColumn::make('category.name')
                    ->sortable(),

                TextColumn::make('brand.name')
                    ->sortable(),

                TextColumn::make('price')
                    ->money('USD    ')
                    ->sortable(),

                IconColumn::make('is_featured')
                    ->boolean(),
                
                IconColumn::make('on_sale')
                    ->boolean(),
                
                IconColumn::make('in_stock')
                    ->boolean(),
                
                IconColumn::make('is_active')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault:true),
                
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault:true),

            ])
            ->filters([
                SelectFilter::make('category')
                    ->relationship('category', 'name'),
                SelectFilter::make('brand')
                    ->relationship('brand', 'name'),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),       
            ])
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}