<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductTransactionResource\Pages;

use App\Enums\ProductTransactionTypeEnum;
use App\Filament\Resources\ProductTransactionResource;
use App\Models\Product;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateProductTransaction extends CreateRecord
{
    protected static string $resource = ProductTransactionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        if ($this->data['type'] === ProductTransactionTypeEnum::BUY->value) {
            Product::query()
                ->where('id', $this->data['product_id'])
                ->increment('in_stock', (int) $this->data['quantity']);

            $this->updateDescription();

            return;
        }

        if ($this->data['type'] === ProductTransactionTypeEnum::SALE->value) {
            Product::query()
                ->where('id', $this->data['product_id'])
                ->decrement('in_stock', (int) $this->data['quantity']);

            $this->updateDescription();

            return;
        }

        if ($this->data['type'] === ProductTransactionTypeEnum::INVENTORY->value) {
            $this->updateStock();
            $this->updateDescription();

            return;
        }

        Notification::make()
            ->title("Erro ao realizar movimentação")
            ->body("Selecione uma opção de movimentação válida")
            ->warning()
            ->persistent()
            ->send();
    }

    private function updateStock(): void
    {
        Product::query()
            ->where('id', $this->data['product_id'])
            ->update(['in_stock' => (int) $this->data['quantity']]);
    }

    private function updateDescription(): void
    {
        $message = 'O usuário '.Auth::user()->name." realizou a movimentação de {$this->getType()} de {$this->data['quantity']} unidades.";

        $this->getRecord()->update(['description' => $message]);
    }

    private function getType(): string
    {
        if ($this->data['type'] === 'buy') {
            return 'compra';
        }

        if ($this->data['type'] === 'sale') {
            return 'venda';
        }

        return 'inventário';
    }
}
