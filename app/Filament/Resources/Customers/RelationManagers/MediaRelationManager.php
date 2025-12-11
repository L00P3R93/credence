<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;

class MediaRelationManager extends RelationManager
{
    protected static string $relationship = 'media';
    protected static ?string $title = 'File Attachments';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            SpatieMediaLibraryFileUpload::make('file')
                ->collection('documents')
                ->label('Upload File')
                ->multiple()
                ->imagePreviewHeight('200')
                ->columnSpanFull()
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('file_name')
            ->columns([
                TextColumn::make('mime_type')
                    ->label('File Type')
                    ->formatStateUsing(fn ($record) => match (true) {
                        str_starts_with($record->mime_type, 'image/') => '🖼️ Image',
                        str_contains($record->mime_type, 'pdf') => '📄 PDF Document',
                        str_contains($record->mime_type, 'word') => '📝 Word Document',
                        str_contains($record->mime_type, 'excel'),
                        str_contains($record->mime_type, 'spreadsheet') => '📊 Excel Sheet',
                        str_contains($record->mime_type, 'zip'),
                        str_contains($record->mime_type, 'compressed') => '🗜️ Compressed File',
                        default => '📁 File',
                    })
                    ->tooltip(fn ($record) => $record->mime_type),

                ViewColumn::make('file_name')
                    ->view('filament.tables.columns.file-name')
                    ->label('File Name'),
                // File size
                TextColumn::make('size')
                    ->formatStateUsing(fn ($record) => number_format($record->size / 1024, 1) . ' KB'),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d M Y H:i')
                    ->sortable()
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon(Heroicon::OutlinedDocumentPlus)
                    ->label('Upload Attachment')
                    ->modalHeading('Upload Customer Attachment')
                    ->modalDescription('Upload a new customer attachment')
                    ->modalIcon(Heroicon::OutlinedDocumentPlus),
            ])
            ->recordActions([
                //EditAction::make()->icon(Heroicon::OutlinedPencil)->iconButton()->color('warning')->tooltip('Edit'),
                DeleteAction::make()->icon(Heroicon::OutlinedTrash)->iconButton()->color('danger')->tooltip('Delete'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
