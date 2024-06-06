<?php

namespace AWF\Extension\Helpers\Models;

use App\Models\OPERATOR_PANEL;
use Illuminate\Database\Eloquent\Collection;

class SequenceResponseModel extends ObjectToArray
{
    protected int $SEQUID;
    protected string|null $ORCODE = null;
    protected string|null $SESIDE = null;
    protected string|null $SEPONR = null;
    protected string|null $SESCRA = null;
    protected string|null $OPNAME = null;
    protected string|null $RNREPN = null;
    protected string|null $DHIDEN = null;
    protected string|null $plc = null;
    protected string|null $plc2 = null;
    protected string|null $pruftisch = null;
    protected array $previousRepnos = [];

    public function getSEQUID(): int
    {
        return $this->SEQUID;
    }

    public function setSEQUID(int $SEQUID): SequenceResponseModel
    {
        $this->SEQUID = $SEQUID;
        return $this;
    }

    public function getORCODE(): string|null
    {
        return $this->ORCODE;
    }

    public function setORCODE(string|null $ORCODE): SequenceResponseModel
    {
        $this->ORCODE = $ORCODE;
        return $this;
    }

    public function getSESIDE(): string|null
    {
        return $this->SESIDE;
    }

    public function setSESIDE(string|null $SESIDE): SequenceResponseModel
    {
        $this->SESIDE = $SESIDE;
        return $this;
    }

    public function getSEPONR(): string|null
    {
        return $this->SEPONR;
    }

    public function setSEPONR(string|null $SEPONR): SequenceResponseModel
    {
        $this->SEPONR = $SEPONR;
        return $this;
    }

    public function getSESCRA(): string|null
    {
        return $this->SESCRA;
    }

    public function setSESCRA(string|null $SESCRA): SequenceResponseModel
    {
        $this->SESCRA = $SESCRA;
        return $this;
    }

    public function getOPNAME(): null|string
    {
        return $this->OPNAME;
    }

    public function setOPNAME(OPERATOR_PANEL|null $operatorPanel): SequenceResponseModel
    {
        if ($operatorPanel !== null) {
            $this->OPNAME = $operatorPanel->OPNAME;

            $this->setDHIDEN($operatorPanel->dashboard->DHIDEN);
        }

        return $this;
    }

    public function getRNREPN(): string|null
    {
        return $this->RNREPN;
    }

    public function setRNREPN(string|null $RNREPN): SequenceResponseModel
    {
        $this->RNREPN = $RNREPN;
        return $this;
    }

    public function getDHIDEN(): string|null
    {
        return $this->DHIDEN;
    }

    public function setDHIDEN(string|null $DHIDEN): SequenceResponseModel
    {
        $this->DHIDEN = $DHIDEN;
        return $this;
    }

    public function getPlc(): string|null
    {
        return $this->plc;
    }

    public function setPlc(string|null $plc): SequenceResponseModel
    {
        $this->plc = $plc;
        return $this;
    }

    public function getPlc2(): string|null
    {
        return $this->plc2;
    }

    public function setPlc2(string|null $plc): SequenceResponseModel
    {
        $this->plc2 = $plc;
        return $this;
    }

    public function getPruftisch(): string|null
    {
        return $this->pruftisch;
    }

    public function setPruftisch(string|null $pruftisch): SequenceResponseModel
    {
        $this->pruftisch = $pruftisch;
        return $this;
    }

    public function getPreviousRepnos(): array
    {
        return $this->previousRepnos;
    }

    public function setPreviousRepnos(Collection $previousRepnos): SequenceResponseModel
    {
        if (!empty($previousRepnos) && !empty($previousRepnos[0])) {
            foreach ($previousRepnos as $previousRepno) {
                $this->previousRepnos[] = [
                    'ORCODE' => $previousRepno->ORCODE,
                    'RNREPN' => $previousRepno->RNREPN,
                    'RNOLMU' => 1,
                    'type' => 'unassigned',
                ];
            }
        }
        return $this;
    }
}
