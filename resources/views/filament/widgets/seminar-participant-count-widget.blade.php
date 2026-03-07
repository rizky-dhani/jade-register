<?php $data = $this->getParticipantsData(); ?>

<div class="grid grid-cols-4 gap-4">
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <div class="text-sm text-gray-500">Pending (No Proof)</div>
        <div class="text-2xl font-bold text-gray-800">{{ $data['pending'] }}</div>
    </div>
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <div class="text-sm text-gray-500">Need to be Checked</div>
        <div class="text-2xl font-bold text-yellow-600">{{ $data['needToBeChecked'] }}</div>
    </div>
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <div class="text-sm text-gray-500">Verified</div>
        <div class="text-2xl font-bold text-green-600">{{ $data['verified'] }}</div>
    </div>
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <div class="text-sm text-gray-500">Total</div>
        <div class="text-2xl font-bold text-gray-800">{{ $data['total'] }}</div>
    </div>
</div>
