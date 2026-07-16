<?php

namespace App\Domains\Shared\Domain\Enums;

/**
 * Canonical permission strings, matching pamora-foundation's
 * 02-product/09-permission-catalog.md exactly. Only the domains
 * implemented so far (Occasion, People, Planning, Finance) have
 * cases — add more as their owning domain is implemented, using the
 * exact string already reserved in the Permission Catalog.
 */
enum Permission: string
{
    // Occasion
    case OccasionEdit = 'occasion.edit';
    case OccasionManageSettings = 'occasion.manage_settings';
    case OccasionArchive = 'occasion.archive';
    case OccasionCancel = 'occasion.cancel';
    case OccasionTransferOwnership = 'occasion.transfer_ownership';

    // People
    case PeopleInviteMember = 'people.invite_member';
    case PeopleRemoveMember = 'people.remove_member';
    case PeopleAssignResponsibility = 'people.assign_responsibility';
    case PeopleManagePermissions = 'people.manage_permissions';

    // Planning
    case PlanningCreateTask = 'planning.create_task';
    case PlanningEditTask = 'planning.edit_task';
    case PlanningAssignTask = 'planning.assign_task';
    case PlanningCompleteTask = 'planning.complete_task';
    case PlanningReopenTask = 'planning.reopen_task';
    case PlanningManageChecklist = 'planning.manage_checklist';
    case PlanningManageMilestone = 'planning.manage_milestone';
    case PlanningManageTimeline = 'planning.manage_timeline';

    // Finance
    case FinanceViewBudget = 'finance.view_budget';
    case FinanceEditBudget = 'finance.edit_budget';
    case FinanceRecordContribution = 'finance.record_contribution';
    case FinanceRecordExpense = 'finance.record_expense';

    // Communication
    case CommunicationPublishAnnouncement = 'communication.publish_announcement';
    case CommunicationScheduleReminder = 'communication.schedule_reminder';

    // Media
    case MediaUpload = 'media.upload';

    /**
     * The full set of permissions granted to a Host on Occasion creation.
     *
     * @return list<string>
     */
    public static function hostDefaults(): array
    {
        return [
            self::OccasionEdit->value,
            self::OccasionManageSettings->value,
            self::OccasionArchive->value,
            self::OccasionCancel->value,
            self::OccasionTransferOwnership->value,
            self::PeopleInviteMember->value,
            self::PeopleRemoveMember->value,
            self::PeopleAssignResponsibility->value,
            self::PeopleManagePermissions->value,
            self::PlanningCreateTask->value,
            self::PlanningEditTask->value,
            self::PlanningAssignTask->value,
            self::PlanningCompleteTask->value,
            self::PlanningReopenTask->value,
            self::PlanningManageChecklist->value,
            self::PlanningManageMilestone->value,
            self::PlanningManageTimeline->value,
            self::FinanceViewBudget->value,
            self::FinanceEditBudget->value,
            self::FinanceRecordContribution->value,
            self::FinanceRecordExpense->value,
            self::CommunicationPublishAnnouncement->value,
            self::CommunicationScheduleReminder->value,
            self::MediaUpload->value,
        ];
    }
}
