<?php

namespace App\Filament\Resources\Vendors\Tables;

use App\Domains\Marketplace\Application\Services\ApproveVendorService;
use App\Domains\Marketplace\Application\Services\RejectVendorService;
use App\Domains\Marketplace\Domain\Enums\VendorVerificationStatus;
use App\Domains\Marketplace\Domain\Models\Vendor;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VendorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('business_name')
                    ->searchable(),
                TextColumn::make('owner.name')
                    ->label('Owner')
                    ->searchable(),
                TextColumn::make('categories')
                    ->badge(),
                TextColumn::make('verification_status')
                    ->badge(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('verification_status')
                    ->options(fn () => collect(VendorVerificationStatus::cases())
                        ->mapWithKeys(fn (VendorVerificationStatus $status) => [$status->value => $status->label()])),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('approve')
                    ->requiresConfirmation()
                    ->visible(fn (Vendor $record): bool => $record->verification_status === VendorVerificationStatus::Pending)
                    ->action(function (Vendor $record) {
                        app(ApproveVendorService::class)->handle($record, auth()->user());

                        Notification::make()
                            ->title('Vendor approved')
                            ->success()
                            ->send();
                    }),
                Action::make('reject')
                    ->requiresConfirmation()
                    ->color('danger')
                    ->visible(fn (Vendor $record): bool => $record->verification_status === VendorVerificationStatus::Pending)
                    ->action(function (Vendor $record) {
                        app(RejectVendorService::class)->handle($record, auth()->user());

                        Notification::make()
                            ->title('Vendor rejected')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
