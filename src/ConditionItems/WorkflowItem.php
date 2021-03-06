<?php

namespace Exceedone\Exment\ConditionItems;

use Exceedone\Exment\Enums\FilterOption;
use Exceedone\Exment\Enums\FilterKind;
use Exceedone\Exment\Enums\FilterType;
use Exceedone\Exment\Enums\SystemColumn;

class WorkflowItem extends SystemItem implements ConditionItemInterface
{
    public function getFilterOption()
    {
        $target = explode('?', $this->target)[0];
        return array_get($this->filterKind == FilterKind::VIEW ? FilterOption::FILTER_OPTIONS() : FilterOption::FILTER_CONDITION_OPTIONS(), $target == SystemColumn::WORKFLOW_STATUS ? FilterType::WORKFLOW : FilterType::WORKFLOW_WORK_USER);
    }
}
