<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use Filament\Schemas\Components\Grid;
use App\Models\Category;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;

class CategoryResource extends Resource

{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Tag;

    protected static ?string $recordTitleAttribute = 'Category';

    public static function form(Schema $schema): Schema
    {
        return $schema
        ->schema([
            Section::make([
                Grid::make()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(String $operation, $state, $set ) => $operation  === 'create' ? $set('slug', str()->slug($state)) : null),

                        TextInput::make('slug')
                            ->disabled()
                            ->maxLength(255)
                            ->required()
                            ->dehydrated()
                            ->unique(Category::class, 'slug', ignoreRecord: true),
                    ]),
                    FileUpload::make('image')
                        ->image()
                        ->directory('categories'),

                        Toggle::make('is_active')
                            ->required()
                            ->default(true),
            ])
        ]);
    }



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                ImageColumn::make('image'),
                TextColumn::make('slug')->searchable(),

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
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),       
            ])
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}