<?php

namespace GPX\Model\SalesForce;

class Interval implements \JsonSerializable {

    public ?string $ID;
    public ?string $Name;
    public ?string $Property_Owner__c;
    public ?string $Room_Type__c;
    public ?string $Week_Type__c;
    public int $Owner_ID__c;
    public int $Contract_ID__c;
    public ?string $GPR_Owner_ID__c;
    public ?string $GPR_Resort__c;
    public ?string $ROID_Key_Full__c;
    public ?string $GPR_Resort_Name__c;
    public ?string $Owner_Status__c;
    public ?string $Resort_ID_v2__c;
    public ?string $UnitWeek__c;
    public ?string $Usage__c;
    public ?int $Year_Last_Banked__c;
    public float $Days_Past_Due__c = 0.0;
    public ?string $Delinquent__c;

    public function __construct( \stdClass $interval ) {
        $this->ID = $interval->ID ?? null;
        $this->Name = $interval->Name ?? null;
        $this->Property_Owner__c = $interval->Property_Owner__c ?? null;
        $this->Room_Type__c = $interval->Room_Type__c ?? null;
        $this->Week_Type__c = $interval->Week_Type__c ?? null;
        $this->Owner_ID__c = $interval->Owner_ID__c;
        $this->Contract_ID__c = $interval->Contract_ID__c;
        $this->GPR_Owner_ID__c = $interval->GPR_Owner_ID__c ?? null;
        $this->GPR_Resort__c = $interval->GPR_Resort__c ?? null;
        $this->ROID_Key_Full__c = $interval->ROID_Key_Full__c ?? null;
        $this->GPR_Resort_Name__c = $interval->GPR_Resort_Name__c ?? null;
        $this->Owner_Status__c = $interval->Owner_Status__c ?? null;
        $this->Resort_ID_v2__c = $interval->Resort_ID_v2__c ?? null;
        $this->UnitWeek__c = $interval->UnitWeek__c ?? null;
        $this->Usage__c = $interval->Usage__c ?? null;
        $this->Year_Last_Banked__c = (int) ( $interval->Year_Last_Banked__c ?? 0 ) ?: null;
        $this->Days_Past_Due__c = isset( $interval->Days_Past_Due__c ) ? (float) $interval->Days_Past_Due__c : 0.0;
        $this->Delinquent__c = $interval->Delinquent__c ?? null;
    }

    public function isDeliquent(): bool {
        return $this->Delinquent__c !== 'No';
    }

    public function toArray(): array {
        return [
            'ID' => $this->ID,
            'Name' => $this->Name,
            'Property_Owner__c' => $this->Property_Owner__c,
            'Room_Type__c' => $this->Room_Type__c,
            'Week_Type__c' => $this->Week_Type__c,
            'Owner_ID__c' => $this->Owner_ID__c,
            'Contract_ID__c' => $this->Contract_ID__c,
            'GPR_Owner_ID__c' => $this->GPR_Owner_ID__c,
            'GPR_Resort__c' => $this->GPR_Resort__c,
            'ROID_Key_Full__c' => $this->ROID_Key_Full__c,
            'GPR_Resort_Name__c' => $this->GPR_Resort_Name__c,
            'Owner_Status__c' => $this->Owner_Status__c,
            'Resort_ID_v2__c' => $this->Resort_ID_v2__c,
            'UnitWeek__c' => $this->UnitWeek__c,
            'Usage__c' => $this->Usage__c,
            'Year_Last_Banked__c' => $this->Year_Last_Banked__c,
            'Days_Past_Due__c' => $this->Days_Past_Due__c,
            'Delinquent__c' => $this->Delinquent__c,
            'is_deliquent' => $this->isDeliquent(),
        ];
    }

    public function jsonSerialize() {
        return $this->toArray();
    }
}
