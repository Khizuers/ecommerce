<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Dom\Text;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AddressRelationManager extends RelationManager
{
    protected static string $relationship = 'address';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('phone')
                    ->required()
                    ->tel()
                    ->maxLength(20),

                TextInput::make('city')
                    ->required()
                    ->maxLength(255),

                TextInput::make('state')
                    ->required()
                    ->maxLength(255),

                TextInput::make('zip_code')
                    ->required()
                    ->numeric()
                    ->maxLength(10),


                TextInput::make('street_address')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('street_address')
            ->columns([
                TextColumn::make('fullname')
                ->label('Full Name'),
                

                TextColumn::make('phone'),

                TextColumn::make('city'),
                
                TextColumn::make('state'),

                TextColumn::make('zip_code'),

                TextColumn::make('street_address'),
                    
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
