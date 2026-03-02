<?php

namespace App\Livewire\QueueMaster;

use App\Models\Office;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class OfficeManage extends Component
{
    public Office $office;

    public function mount(string $office)
    {
        $this->office = Office::where('slug', $office)->firstOrFail();
    }

    public function getQrCodeSvg()
    {
        $url = $this->office->getQueueJoinUrl();
        try {
            $qrcode = app('qrcode');
            return $qrcode->size(200)->generate($url);
        } catch (\Throwable $e) {
            return '<svg width="200" height="200"><text x="10" y="100">QR error</text></svg>';
        }
    }

    public function render()
    {
        return view('livewire.queue-master.office-manage', [
            'qrSvg' => $this->getQrCodeSvg(),
        ]);
    }
}
