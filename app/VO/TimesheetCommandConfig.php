<?php

namespace App\VO;

use Carbon\Carbon;

class TimesheetCommandConfig
{
    /**
     * Date object representing the month of the timesheet being generated
     *
     * @var Carbon
     */
    public $generationDate;

    /**
     * Cell where the month will be
     *
     * @var string
     */
    public $monthHeaderCell;

    /**
     * Cell where the person name will be
     *
     * @var string
     */
    public $personNameCell;

    /**
     * String with the formatation that month header needs to be
     *
     * @var string
     */
    public $monthHeaderFormat;

    /**
     * String with the path to the template file
     *
     * @var string
     */
    public $templateFile;

    /**
     * Initial Row Number
     *
     * @var int
     */
    public $initialRow;

    /**
     * Initial Column Letter
     *
     * @var string
     */
    public $initialCol;

    /**
     * Array with recipient email addresses strings
     *
     * @var string[]
     */
    public $recipientAddresses;

    /**
     * The amount of hours to target the timesheet generation
     *
     * @var int
     */
    public $targetHours;

    /**
     * Creates a new configuration object to be used by timesheet generation jobs
     *
     * @param Carbon $generationDate
     * @param string $monthHeaderCell
     * @param string $personNameCell
     * @param string $monthHeaderFormat
     * @param string $templateFile
     * @param integer $initialRow
     * @param string $initialCol
     * @param array $recipientAddresses
     */
    public function __construct(
        Carbon $generationDate,
        string $monthHeaderCell,
        string $personNameCell,
        string $monthHeaderFormat,
        string $templateFile,
        int $initialRow,
        string $initialCol,
        array $recipientAddresses,
        int $targetHours
    ) {
        $this->generationDate = $generationDate;
        $this->monthHeaderCell = $monthHeaderCell;
        $this->personNameCell = $personNameCell;
        $this->monthHeaderFormat = $monthHeaderFormat;
        $this->templateFile = $templateFile;
        $this->initialRow = $initialRow;
        $this->initialCol = $initialCol;
        $this->recipientAddresses = $recipientAddresses;
        $this->targetHours = $targetHours;
    }
}
