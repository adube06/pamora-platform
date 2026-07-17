<?php

namespace App\Domains\People\Domain\Enums;

use App\Domains\Shared\Domain\Enums\Permission;

/**
 * The single source of truth for "what can this OccasionMember do,"
 * given as: Role -> Permission Catalog -> resolved permissions.
 *
 * A Role is a named permission template chosen when someone is invited
 * (or, for the Host, assigned automatically). It is not a runtime
 * authorization mechanism by itself — every Policy still checks
 * OccasionMember::hasPermission() against the resolved Permission
 * strings, never against the Role name. This keeps People PRD Section
 * 7's instruction intact ("permissions assigned through capabilities,
 * not hard-coded roles") while giving the Committee invite flow a
 * single required field instead of an open-ended permission picker.
 */
enum Role: string
{
    case Host = 'host';
    case Chairperson = 'chairperson';
    case Treasurer = 'treasurer';
    case Secretary = 'secretary';
    case Coordinator = 'coordinator';
    case Member = 'member';
    case Observer = 'observer';
    case Guest = 'guest';

    public function label(): string
    {
        return match ($this) {
            self::Host => 'Host',
            self::Chairperson => 'Chairperson',
            self::Treasurer => 'Treasurer',
            self::Secretary => 'Secretary',
            self::Coordinator => 'Coordinator',
            self::Member => 'Member',
            self::Observer => 'Observer',
            self::Guest => 'Guest',
        };
    }

    /**
     * The resolved Permission Catalog strings for this Role. Sensitive,
     * ownership-level actions (archive, cancel, transfer ownership,
     * remove member, manage permissions) stay Host-exclusive by
     * default — every other Role starts conservative and is widened
     * deliberately, not by default.
     *
     * @return list<string>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::Host => Permission::hostDefaults(),

            self::Chairperson => [
                Permission::OccasionEdit->value,
                Permission::OccasionManageSettings->value,
                Permission::PeopleInviteMember->value,
                Permission::PeopleAssignResponsibility->value,
                Permission::PlanningCreateTask->value,
                Permission::PlanningEditTask->value,
                Permission::PlanningAssignTask->value,
                Permission::PlanningCompleteTask->value,
                Permission::PlanningReopenTask->value,
                Permission::PlanningManageChecklist->value,
                Permission::PlanningManageMilestone->value,
                Permission::PlanningManageTimeline->value,
                Permission::CommunicationPublishAnnouncement->value,
                Permission::CommunicationScheduleReminder->value,
                Permission::MediaUpload->value,
                Permission::MediaEditMetadata->value,
                Permission::MarketplaceRequestQuotation->value,
            ],

            self::Treasurer => [
                Permission::PlanningCreateTask->value,
                Permission::PlanningEditTask->value,
                Permission::PlanningCompleteTask->value,
                Permission::PlanningReopenTask->value,
                Permission::FinanceViewBudget->value,
                Permission::FinanceEditBudget->value,
                Permission::FinanceRecordPledge->value,
                Permission::FinanceRecordContribution->value,
                Permission::FinanceRecordExpense->value,
                Permission::MediaUpload->value,
                Permission::MediaEditMetadata->value,
            ],

            self::Secretary => [
                Permission::PlanningCreateTask->value,
                Permission::PlanningEditTask->value,
                Permission::PlanningManageChecklist->value,
                Permission::PlanningManageMilestone->value,
                Permission::PlanningManageTimeline->value,
                Permission::CommunicationScheduleReminder->value,
                Permission::MediaUpload->value,
                Permission::MediaEditMetadata->value,
            ],

            self::Coordinator => [
                Permission::PlanningCreateTask->value,
                Permission::PlanningAssignTask->value,
                Permission::PlanningCompleteTask->value,
                Permission::PlanningReopenTask->value,
                Permission::MediaUpload->value,
                Permission::MediaEditMetadata->value,
            ],

            self::Member => [
                Permission::PlanningCompleteTask->value,
                Permission::PlanningReopenTask->value,
                Permission::MediaUpload->value,
                Permission::MediaEditMetadata->value,
            ],

            // View-only by design — no permissions.
            self::Observer => [],

            // An invited attendee, not a committee member — responds via
            // RSVP rather than organizing. Same empty-permission shape as
            // Observer, but semantically distinct.
            self::Guest => [],
        };
    }
}
