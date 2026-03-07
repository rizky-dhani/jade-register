<?php $data = $this->getVisitorsData(); ?>

<div class="grid grid-cols-4 gap-4">
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <div class="text-sm text-gray-500">Day 1 (Nov 13)</div>
        <div class="text-2xl font-bold text-gray-800">{{ $data['day1'] }}</div>
    </div>
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <div class="text-sm text-gray-500">Day 2 (Nov 14)</div>
        <div class="text-2xl font-bold text-gray-800">{{ $data['day2'] }}</div>
    </div>
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <div class="text-sm text-gray-500">Day 3 (Nov 15)</div>
        <div class="text-2xl font-bold text-gray-800">{{ $data['day3'] }}</div>
    </div>
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <div class="text-sm text-gray-500">Total</div>
        <div class="text-2xl font-bold text-gray-800">{{ $data['total'] }}</div>
    </div>
</div>
