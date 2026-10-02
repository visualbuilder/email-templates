<?php

declare(strict_types=1);

namespace Visualbuilder\EmailTemplates\Resources;

use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Visualbuilder\EmailTemplates\EmailTemplatesPlugin;
use Visualbuilder\EmailTemplates\Models\EmailBlock;
use Visualbuilder\EmailTemplates\Resources\EmailBlockResource\Pages;
use Visualbuilder\EmailTemplates\Resources\Schemas\EmailLayoutBuilder;

/**
 * Block library: reusable groups of email blocks, inserted into template
 * layouts through a "Saved block" item.
 */
class EmailBlockResource extends Resource
{
    protected static ?string $model = EmailBlock::class;

    public static function shouldRegisterNavigation(): bool
    {
        return EmailTemplatesPlugin::get()->shouldRegisterNavigation();
    }

    public static function getNavigationIcon(): ?string
    {
        return config('filament-email-templates.navigation.blocks.icon');
    }

    public static function getNavigationGroup(): ?string
    {
        return EmailTemplatesPlugin::get()->getNavigationGroup();
    }

    public static function getNavigationSort(): ?int
    {
        return config('filament-email-templates.navigation.blocks.sort');
    }

    public static function getModelLabel(): string
    {
        return __(config('filament-email-templates.navigation.blocks.label', 'Email Blocks'));
    }

    public static function getPluralModelLabel(): string
    {
        return __(config('filament-email-templates.navigation.blocks.label', 'Email Blocks'));
    }

    public static function getCluster(): ?string
    {
        return static::$cluster ?? (config('filament-email-templates.navigation.blocks.cluster') ?: null);
    }

    public static function getSubNavigationPosition(): SubNavigationPosition
    {
        return config('filament-email-templates.navigation.blocks.position', SubNavigationPosition::Top);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make()
                ->columnSpanFull()
                ->schema([
                    TextInput::make('name')
                        ->label(__('Name'))
                        ->required()
                        ->maxLength(120)
                        ->unique(ignoreRecord: true),
                    TextInput::make('description')
                        ->label(__('Description'))
                        ->maxLength(255),
                    Toggle::make('is_active')
                        ->label(__('Available in the composer'))
                        ->default(true),
                    EmailLayoutBuilder::make('layout', allowSavedBlocks: false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label(__('Description'))
                    ->limit(60),
                IconColumn::make('is_active')
                    ->label(__('Available'))
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label(__('Updated'))
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('Available in the composer')),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->modalDescription(__('Templates using this block will send without it.')),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()
                    ->modalDescription(__('Templates using these blocks will send without them.')),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailBlocks::route('/'),
            'create' => Pages\CreateEmailBlock::route('/create'),
            'edit' => Pages\EditEmailBlock::route('/{record}/edit'),
        ];
    }
}
