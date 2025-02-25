<?php

namespace App\Filament\Resources\SectionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ComponentsRelationManager extends RelationManager
{
    // Определяем имя отношения в модели
    protected static string $relationship = 'components';

    // Определяем поле, которое будет использоваться как заголовок записи
    protected static ?string $recordTitleAttribute = 'title';

    // Альтернативный способ определения заголовка
    // public function getRecordTitle(?Model $record): string
    // {
    //     return $record?->title ?? 'New Component';
    // }

    // Дополнительно можно указать иконку и лейбл для вкладки
    protected static ?string $icon = 'heroicon-o-puzzle-piece';
    protected static ?string $label = 'Компоненты';
    protected static ?string $pluralLabel = 'Компоненты';

    // Количество записей на странице
    protected int $defaultPaginationPageOption = 10;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->options([
                        'hero' => 'Hero/Banner',
                        'text' => 'Text',
                        'image' => 'Image',
                        'features' => 'Features',
                        'cta' => 'Call to Action',
                        'testimonials' => 'Testimonials',
                        'pricing' => 'Pricing',
                        'faq' => 'FAQ',
                        'contact' => 'Contact Form',
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                        // Можно изменять другие поля в зависимости от выбранного типа
                        if ($operation === 'create' && $state === 'hero') {
                            $set('sort_order', 0); // Hero компоненты обычно идут первыми
                        }
                    }),

                Forms\Components\TextInput::make('title')
                    ->maxLength(255),

                Forms\Components\TextInput::make('subtitle')
                    ->maxLength(255),

                // Пример условного отображения полей в зависимости от типа компонента
                Forms\Components\RichEditor::make('content')
                    ->visible(fn (Forms\Get $get): bool => in_array($get('type'), ['text', 'features', 'faq']))
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('uploads')
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('image_path')
                    ->visible(fn (Forms\Get $get): bool => in_array($get('type'), ['hero', 'image', 'features']))
                    ->image()
                    ->directory('section-images')
                    ->visibility('public'),

                Forms\Components\Group::make()
                    ->visible(fn (Forms\Get $get): bool => in_array($get('type'), ['hero', 'cta']))
                    ->schema([
                        Forms\Components\TextInput::make('button_text')
                            ->maxLength(50),

                        Forms\Components\TextInput::make('button_link')
                            ->url()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Toggle::make('is_active')
                    ->default(true)
                    ->helperText('Отображать компонент на странице'),

                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Порядок отображения (0 - в начале)'),

                // Дополнительные настройки компонента в JSON формате
                Forms\Components\KeyValue::make('settings')
                    ->keyLabel('Параметр')
                    ->valueLabel('Значение')
                    ->columnSpanFull()
                    ->addActionLabel('Добавить параметр')
                    ->helperText('Дополнительные настройки для компонента'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'hero' => 'primary',
                        'cta' => 'success',
                        'features' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('title'),

                Tables\Columns\ImageColumn::make('image_path')
                    ->circular()
                    ->visibility('public'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Примеры фильтров
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'hero' => 'Hero/Banner',
                        'text' => 'Text',
                        'image' => 'Image',
                        'features' => 'Features',
                        'cta' => 'Call to Action',
                        'testimonials' => 'Testimonials',
                        'pricing' => 'Pricing',
                        'faq' => 'FAQ',
                        'contact' => 'Contact Form',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активные'),
            ])
            ->headerActions([
                // Действия в заголовке таблицы
                Tables\Actions\CreateAction::make()
                    ->label('Добавить компонент'),

                // Дополнительный пример - импорт компонентов
                Tables\Actions\Action::make('import')
                    ->label('Импорт')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->action(function (array $data) {
                        // Здесь можно реализовать логику импорта
                    }),
            ])
            ->actions([
                // Действия для каждой записи
                Tables\Actions\EditAction::make()
                    ->modalWidth('xl'),

                Tables\Actions\DeleteAction::make(),

                // Пример кастомного действия
                Tables\Actions\Action::make('duplicate')
                    ->label('Дублировать')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function ($record) {
                        $newComponent = $record->replicate();
                        $newComponent->title = 'Копия ' . $newComponent->title;
                        $newComponent->save();
                    }),
            ])
            ->bulkActions([
                // Массовые действия
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    // Пример кастомного массового действия
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Активировать')
                        ->icon('heroicon-o-check')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->is_active = true;
                                $record->save();
                            }
                        }),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Деактивировать')
                        ->icon('heroicon-o-x-mark')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->is_active = false;
                                $record->save();
                            }
                        }),
                ]),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->paginated([5, 10, 25, 50]);
    }
}
