<?php

namespace App\Livewire\Dashboard;

use App\Models\HourMeterLog;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\Unit;
use App\Support\HfsNavigation;
use Livewire\Component;

class ExecutiveDashboard extends Component
{
    public function render()
    {
        $fuelAlerts = HourMeterLog::query()
            ->with('unit')
            ->where('is_fuel_flagged', true)
            ->latest('input_date')
            ->take(5)
            ->get();

        $serviceAlerts = Unit::query()
            ->whereHas('latestHourMeterLog', fn ($query) => $query->where('service_alert_level', '!=', 'normal'))
            ->with(['site', 'latestHourMeterLog'])
            ->take(6)
            ->get();

        return view('livewire.dashboard.executive-dashboard', [
            'metrics' => [
                'units' => Unit::count(),
                'fuel_alerts' => HourMeterLog::where('is_fuel_flagged', true)->count(),
                'open_pr' => PurchaseRequisition::whereNotIn('status', ['approved', 'closed', 'rejected'])->count(),
                'open_po' => PurchaseOrder::whereNotIn('status', ['received', 'closed', 'cancelled'])->count(),
            ],
            'fuelAlerts' => $fuelAlerts,
            'serviceAlerts' => $serviceAlerts,
            'navigation' => HfsNavigation::forUser(auth()->user()),
        ]);
    }
}
