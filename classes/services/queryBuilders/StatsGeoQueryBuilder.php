<?php

/**
 * @file classes/services/queryBuilders/StatsGeoQueryBuilder.php
 *
 * Copyright (c) 2022 Simon Fraser University
 * Copyright (c) 2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class StatsGeoQueryBuilder
 *
 * @ingroup query_builders
 *
 * @brief Helper class to construct a query to fetch geographic stats records from the
 *  metrics_submission_geo_monthly table.
 */

namespace APP\services\queryBuilders;

use APP\submission\Submission;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use PKP\services\queryBuilders\PKPStatsGeoQueryBuilder;
use PKP\statistics\PKPStatisticsHelper;

class StatsGeoQueryBuilder extends PKPStatsGeoQueryBuilder
{
    /** Include records for these issues */
    protected array $issueIds = [];

    public function getSectionColumn(): string
    {
        return 'section_id';
    }

    /**
     * Set the issues to get records for
     */
    public function filterByIssues(array $issueIds): self
    {
        $this->issueIds = $issueIds;
        return $this;
    }

    protected function _getAppSpecificQuery(Builder &$q): void
    {
        if (!empty($this->issueIds)) {
            $q->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('publications as p')
                    ->whereRaw('p.submission_id = metrics_submission_geo_monthly.' . PKPStatisticsHelper::STATISTICS_DIMENSION_SUBMISSION_ID)
                    ->where('p.status', Submission::STATUS_PUBLISHED)
                    ->whereIn('p.issue_id', $this->issueIds);
            });
        }
    }
}
