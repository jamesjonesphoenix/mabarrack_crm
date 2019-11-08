<?php

namespace Phoenix\Report;

use DateTime;
use Phoenix\Report;
use function Phoenix\ph_format_table_value;
use function Phoenix\ph_get_template_part;

/**
 * Class JobCosting
 *
 * @package Phoenix\Report
 */
class JobCosting extends Report
{
    /**
     * @var array
     */
    public $job = array();

    /**
     * @var array
     */
    public $activity_summary = array();


    /**
     * @param string $jobID
     * @return bool
     */
    public function init($jobID = ''): bool
    {
        if (empty($jobID)) {
            $this->messages->add('Job ID missing. Can\'t create report.');
            return false;
        }
        $job = $this->get_job($jobID);

        if (empty($job)) {
            $this->messages->add('Job not found. Can\'t create report. Is Job ID : "' . $jobID . '" correct?');
            return false;
        }

        $shifts = $this->get_shifts();
        if (empty($shifts)) {
            $this->messages->add('No shifts found for this job. Can\'t create report.');
            return false;
        }

        //output activities summary table
        $this->activity_summary = $this->setup_activity_summary();
        //output customer hours
        $total_profit = $job['sale_price'] - $this->totals['amount']['employee_cost'] - $job['material_cost'] - $job['contractor_cost'] - $job['spare_cost'];
        $total_cost = $this->totals['amount']['employee_cost'] + $job['material_cost'] + $job['contractor_cost'] + $job['spare_cost'];
        $this->set_a_total($total_profit, 'total_profit', 'money');
        $this->set_a_total($total_cost, 'total_cost', 'money');
        $this->set_a_total($total_profit / $total_cost, 'markup', 'percent');
        $this->set_a_total($total_profit / $this->get_job()['sale_price'], 'gross_margin', 'percent');


        $this->set_a_total($this->totals['amount']['employee_cost'] / $total_cost, 'percent_employee_cost', 'percent');
        $this->set_a_total($job['material_cost'], 'material_cost', 'money');
        $this->set_a_total($job['material_cost'] / $total_cost, 'percent_material_cost', 'percent');
        $this->set_a_total($job['contractor_cost'], 'contractor_cost', 'money');
        $this->set_a_total($job['contractor_cost'] / $total_cost, 'percent_contractor_cost', 'percent');
        $this->set_a_total($job['spare_cost'], 'spare_cost', 'money');
        $this->set_a_total($job['spare_cost'] / $total_cost, 'percent_spare_cost', 'percent');


        $this->set_a_total($job['sale_price'], 'sale_price', 'money');

        return true;
    }

    /**
     * @param int $jobID
     * @return array|bool
     */
    public function get_job(int $jobID = 0)
    {
        if (!empty($this->job)) {
            return $this->job;
        }
        if (empty($jobID)) {
            return false;
        }
        return $this->job = $this->db->run('SELECT jobs.*, customers.name as customer FROM jobs INNER JOIN customers ON jobs.customer=customers.ID WHERE jobs.ID = ?',
            [$jobID])->fetch();
    }

    /**
     * @return array|bool
     */
    public function query_shifts()
    {
        $job = $this->get_job();
        if (empty($job)) {
            return false;
        }

        return $this->db->run('SELECT shifts.*, users.name as worker_name, users.rate 
FROM shifts 
INNER JOIN users ON shifts.worker=users.ID 
WHERE shifts.job = ?',
            [$job['ID']])->fetchAll();
    }

    /**
     * @return bool|mixed
     * @throws \Exception
     */
    public function setup_job_costing()
    {
        $job_costing = array();
        $shifts = $this->get_shifts();
        if (!empty($shifts)) {
            foreach ($shifts as $shift) {

                // Create a new DateTime object
                $dateo = new DateTime($shift['date']);
                // Modify the date it contains
                $dateo->modify('next thursday');

                $job_costing[] = array(
                    'ID' => $shift['ID'],
                    'shift_ID' => $shift['ID'],
                    'worker' => $shift['worker_name'],
                    'W/ending' => $dateo->format('d-m-Y'),
                    'minutes' => $shift['minutes'],
                    'activity' => $shift['activity'],
                    'rate' => $shift['rate'],
                    'line_item_cost' => $shift['cost'],
                );
            }
            $job_costing = ph_format_table_value($job_costing, [
                'minutes' => array('type' => 'hoursminutes', 'output_column' => 'hours'),
                'rate' => array('type' => 'currency'),
                'line_item_cost' => array('type' => 'currency')]);
            return $this->job_costing = $job_costing;
        }
        return false;
    }

    /**
     * @return bool|mixed
     * @throws \Exception
     */
    public function get_job_costing()
    {
        if (!empty($this->job_costing)) {
            return $this->job_costing;
        }
        return $this->setup_job_costing();
    }

    /**
     * @throws \Exception
     */
    public function output_report()
    {
        ph_get_template_part('report/header/links-admin', array());
        if ($this->get_shifts()) {
            ph_get_template_part('report/job-costing/report', array(
                'job' => $this->get_job(),
                'shifts' => $this->get_job_costing(),
                'activities_summary' => $this->activity_summary,
                'totals' => $this->totals['formatted'],
            ));
        }
    }
}