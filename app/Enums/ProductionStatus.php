<?php

namespace App\Enums;

enum ProductionStatus: string
{
    case AwaitingReview = 'awaiting_review';
    case MeasurementsReview = 'measurements_review';
    case Confirmed = 'confirmed';
    case InProduction = 'in_production';
    case QualityCheck = 'quality_check';
    case ReadyToDispatch = 'ready_to_dispatch';
    case Dispatched = 'dispatched';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::AwaitingReview => 'Awaiting Review',
            self::MeasurementsReview => 'Measurements Review',
            self::Confirmed => 'Confirmed',
            self::InProduction => 'In Production',
            self::QualityCheck => 'Quality Check',
            self::ReadyToDispatch => 'Ready to Dispatch',
            self::Dispatched => 'Dispatched',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * @return list<string>
     */
    public function allowedNext(): array
    {
        return match ($this) {
            self::AwaitingReview => [self::MeasurementsReview->value, self::Cancelled->value],
            self::MeasurementsReview => [self::Confirmed->value, self::Cancelled->value],
            self::Confirmed => [self::InProduction->value, self::Cancelled->value],
            self::InProduction => [self::QualityCheck->value, self::Cancelled->value],
            self::QualityCheck => [self::ReadyToDispatch->value, self::InProduction->value],
            self::ReadyToDispatch => [self::Dispatched->value],
            self::Dispatched => [self::Delivered->value],
            self::Delivered, self::Cancelled => [],
        };
    }
}
