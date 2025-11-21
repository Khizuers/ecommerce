<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $recordTitleAttribute = 'name';
    
    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema)
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('email')->email()->required() ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->password()
                    ->required()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state)),
                DatePicker::make('email_verified_at')
                    ->label('Date')
                    ->placeholder('Please fill out')
                    ->required(),
            ]);
    }

    // Use the form in view mode (fields disabled) for the View page
    public static function getViewForm(): Schema
    {
        return UserForm::configure(Schema::make())
            ->schema([
                TextInput::make('name')->label('Name')->disabled(),
                TextInput::make('email')->label('Email')->disabled(),
                DatePicker::make('email_verified_at')->label('Date')->disabled(),
                TextInput::make('created_at')->label('Created At')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table)
            ->columns([
                TextColumn::make('name')->label('Name'),
                TextColumn::make('email')->label('Email'),
                TextColumn::make('email_verified_at')->label('Date')->dateTime(),
                TextColumn::make('created_at')->label('Created At')->dateTime(),
            ]);
    }

    public static function getRelations(): array
    {   
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];  
    }
}
