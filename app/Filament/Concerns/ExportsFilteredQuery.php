<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * The query handed to the registration Excel exports.
 *
 * Filters, search and sort follow the desk as usual, but tabs are deliberately
 * ignored: the workbook is a single file covering every segment, so exporting
 * from the "Local" tab must still include international rows. Tabs narrow the
 * table through a query scope that reads the live `activeTab` property, so that
 * property is blanked for the duration of the call and restored afterwards.
 */
trait ExportsFilteredQuery
{
    public function getTableQueryForExportIgnoringTabs(): Builder
    {
        $activeTab = $this->activeTab;

        try {
            $this->activeTab = null;

            return $this->getTableQueryForExport();
        } finally {
            $this->activeTab = $activeTab;
        }
    }
}
