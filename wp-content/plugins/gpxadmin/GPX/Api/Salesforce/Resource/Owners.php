<?php

namespace GPX\Api\Salesforce\Resource;

class Owners extends AbstractResource {

    public function count_new_owners(): int {
        $sfquery = "SELECT COUNT(Name)
                FROM GPR_Owner_ID__c
                WHERE
                    GPX_Member_VEST__c = null
                    AND Total_Active_Contracts__c > 0
                    AND HOA_Developer__c = false
                    AND SPI_Email__c != null
                    AND Id NOT IN (SELECT GPR_Owner_ID__c FROM Ownership_Interval__c WHERE Resort_ID_v2__c='GPVC')";

        $total = $this->sf->query( $sfquery );

        return $total ? (int) $total[0]->expr0 : 0;
    }

    /** @return \SObject[] */
    public function new_owners( int $limit = 12, int $offset = 0, bool $with_intervals ): array {
        // don't let $offset larger than 2000  - SOQL LIMIT
        $offset = min( $offset, 2000 );

        $sfquery = sprintf( "SELECT CreatedDate,Name,SPI_First_Name__c,SPI_Last_Name__c,
                      Total_Active_Contracts__c,
                      SPI_First_Name2__c,SPI_Last_Name2__c,SPI_Email__c,
                      SPI_Home_Phone__c,SPI_Work_Phone__c,SPI_Street__c,
                      SPI_City__c,SPI_State__c,SPI_Zip_Code__c,
                      SPI_Country__c,SpiOwnerId__c,Property_Owner__c,
                      Legacy_Preferred_Program_Member__c,GPX_Member_VEST__c
                FROM GPR_Owner_ID__c
                WHERE
                    GPX_Member_VEST__c = null
                    AND Total_Active_Contracts__c > 0
                    AND HOA_Developer__c = false
                    AND SPI_Email__c != null

                    AND Id NOT IN (SELECT GPR_Owner_ID__c FROM Ownership_Interval__c WHERE Resort_ID_v2__c='GPVC')
                ORDER BY CreatedDate DESC
                LIMIT %d OFFSET %d",
                            $limit,
                            $offset );
        $owners  = $this->sf->query( $sfquery ) ?? [];
        if ( ! $with_intervals ) {
            return $owners;
        }

        $owners = $this->addIntervals($owners);

        return $owners;
    }

    /** @return \SObject[] */
    public function updated_owners( \DateTimeInterface $modified_since, int $limit = null): array {
        $sfquery =  sprintf("SELECT CreatedDate,Name,SPI_First_Name__c,SPI_Last_Name__c,
                      Total_Active_Contracts__c,
                      SPI_First_Name2__c,SPI_Last_Name2__c,SPI_Email__c,
                      SPI_Home_Phone__c,SPI_Work_Phone__c,SPI_Street__c,
                      SPI_City__c,SPI_State__c,SPI_Zip_Code__c,
                      SPI_Country__c,SpiOwnerId__c,Property_Owner__c,
                      Legacy_Preferred_Program_Member__c,GPX_Member_VEST__c,SystemModStamp
                FROM GPR_Owner_ID__c
                WHERE
                    SystemModStamp >= %s
                    AND GPX_Member_VEST__c != null
                    AND HOA_Developer__c = false
                    AND SPI_Email__c != null
                    AND Id NOT IN (SELECT GPR_Owner_ID__c FROM Ownership_Interval__c WHERE Resort_ID_v2__c='GPVC')
                ORDER BY SystemModStamp ASC", $modified_since->format('c'));
        if($limit > 0) $sfquery .= sprintf(" LIMIT %d", $limit);
        $owners  = $this->sf->query( $sfquery ) ?? [];
        $owners = $this->addIntervals($owners);

        return $owners;
    }

    public function intervals( $owners = [] ) {
        return $this->sf->interval->get_intervals_by_owner( $owners );
    }

    private function addIntervals(array $owners): array
    {
        $intervals = $this->intervals( array_map( fn( $owner ) => $owner->Name, $owners ) );
        foreach ( $owners as $index => $owner ) {
            $owners[ $index ]->intervals = array_filter( $intervals,
                fn( $interval ) => $interval->Owner_ID__c === $owner->Name );
        }
        return $owners;
    }
}
