import { Form, useForm } from '@inertiajs/react';
import Alert from '@/Components/Alert';
import Avatar from '@/Components/Avatar';
import Badge from '@/Components/Badge';
import Button from '@/Components/Button';
import Card from '@/Components/Card';
import FormField from '@/Components/FormField';
import Input from '@/Components/Input';
import Select from '@/Components/Select';
import Textarea from '@/Components/Textarea';
import OccasionWorkspaceLayout from '@/Layouts/OccasionWorkspaceLayout';
import type { Invitation, Occasion, OccasionMember, RoleOption } from '@/types/models';

interface Props {
    occasion: Occasion;
    members: OccasionMember[];
    pendingInvitations: Invitation[];
    canInvite: boolean;
    roles: RoleOption[];
    myMembership: OccasionMember | null;
    canReopenRsvp: boolean;
    canRemoveMember: boolean;
    canTransferOwnership: boolean;
}

const NON_ORGANIZING_ROLES = ['host', 'guest', 'observer'];

function roleLabel(roles: RoleOption[], value: string): string {
    return roles.find((r) => r.value === value)?.label ?? value;
}

const RSVP_BADGE: Record<string, { variant: 'success' | 'error' | 'warning' | 'neutral'; label: string }> = {
    attending: { variant: 'success', label: 'Attending' },
    not_attending: { variant: 'error', label: 'Not Attending' },
    maybe: { variant: 'warning', label: 'Maybe' },
};

function RsvpBadge({ status }: { status: string | null }) {
    const entry = status ? RSVP_BADGE[status] : undefined;

    return <Badge variant={entry?.variant ?? 'neutral'}>{entry?.label ?? 'No response'}</Badge>;
}

function ReopenRsvpButton({ member }: { member: OccasionMember }) {
    const { post, processing } = useForm({});

    return (
        <Button
            variant="ghost"
            size="sm"
            loading={processing}
            onClick={() => post(route('occasion-members.reopen-rsvp', member.uuid), { preserveScroll: true })}
        >
            Reopen
        </Button>
    );
}

function RemoveMemberButton({ member }: { member: OccasionMember }) {
    const { delete: destroy, processing } = useForm({});

    function remove() {
        if (window.confirm(`Remove ${member.user?.name} from this Occasion?`)) {
            destroy(route('occasion-members.destroy', member.uuid), { preserveScroll: true });
        }
    }

    return (
        <Button variant="danger" size="sm" loading={processing} onClick={remove}>
            Remove
        </Button>
    );
}

function TransferOwnershipButton({ occasion, member }: { occasion: Occasion; member: OccasionMember }) {
    const { post, processing } = useForm({ member_uuid: member.uuid });

    function transfer() {
        if (window.confirm(`Make ${member.user?.name} the Host of this Occasion? You will become Chairperson.`)) {
            post(route('occasions.transfer-ownership', occasion.slug), { preserveScroll: true });
        }
    }

    return (
        <Button variant="ghost" size="sm" loading={processing} onClick={transfer}>
            Make Host
        </Button>
    );
}

function YourRsvpCard({ occasion, myMembership }: { occasion: Occasion; myMembership: OccasionMember }) {
    if (myMembership.rsvp_status) {
        return (
            <Card title="Your RSVP">
                <div className="flex items-center gap-2">
                    <RsvpBadge status={myMembership.rsvp_status} />
                </div>
                {myMembership.guest_count !== null && (
                    <p className="mt-2 text-xs text-text-secondary">Guests: {myMembership.guest_count}</p>
                )}
                {myMembership.rsvp_message && <p className="mt-1 text-xs text-text-secondary">"{myMembership.rsvp_message}"</p>}
                <p className="mt-3 text-xs text-text-secondary">Ask your Host to reopen RSVP to change your response.</p>
            </Card>
        );
    }

    return (
        <Card title="Your RSVP">
            <Form action={route('occasions.rsvp.store', occasion.slug)} method="post" className="space-y-3">
                {({ errors, processing }) => (
                    <>
                        <FormField label="Will you attend?" htmlFor="rsvp_status" required error={errors.rsvp_status}>
                            <Select id="rsvp_status" name="rsvp_status" required defaultValue="" invalid={!!errors.rsvp_status}>
                                <option value="" disabled>
                                    Select a response
                                </option>
                                <option value="attending">Attending</option>
                                <option value="not_attending">Not Attending</option>
                                <option value="maybe">Maybe</option>
                            </Select>
                        </FormField>

                        <FormField label="Number of attendees (optional)" htmlFor="guest_count">
                            <Input id="guest_count" name="guest_count" type="number" min={0} />
                        </FormField>

                        <FormField label="Message (optional)" htmlFor="rsvp_message">
                            <Textarea id="rsvp_message" name="rsvp_message" rows={2} />
                        </FormField>

                        <Button type="submit" loading={processing}>
                            {processing ? 'Submitting…' : 'Submit RSVP'}
                        </Button>
                    </>
                )}
            </Form>
        </Card>
    );
}

export default function Committee({
    occasion,
    members,
    pendingInvitations,
    canInvite,
    roles,
    myMembership,
    canReopenRsvp,
    canRemoveMember,
    canTransferOwnership,
}: Props) {
    return (
        <OccasionWorkspaceLayout occasion={occasion} active="committee">
            {myMembership && (
                <div className="mb-6 max-w-md">
                    <YourRsvpCard occasion={occasion} myMembership={myMembership} />
                </div>
            )}

            <div className="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <div className="lg:col-span-2">
                    <h2 className="text-sm font-medium text-text-primary">Members</h2>
                    <ul className="mt-3 divide-y divide-border rounded-lg border border-border bg-surface">
                        {members.map((member) => (
                            <li key={member.id} className="flex items-center justify-between px-4 py-3">
                                <div className="flex items-center gap-3">
                                    <Avatar name={member.user?.name ?? '?'} />
                                    <div>
                                        <p className="text-sm font-medium text-text-primary">{member.user?.name}</p>
                                        <p className="text-xs text-text-secondary">{member.user?.email}</p>
                                        {member.notes && <p className="mt-0.5 text-xs text-text-secondary">{member.notes}</p>}
                                    </div>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Badge>{member.role === 'host' ? 'Host' : roleLabel(roles, member.role)}</Badge>
                                    <RsvpBadge status={member.rsvp_status} />
                                    {canReopenRsvp && member.rsvp_status && <ReopenRsvpButton member={member} />}
                                    {canTransferOwnership && member.status === 'active' && !NON_ORGANIZING_ROLES.includes(member.role) && (
                                        <TransferOwnershipButton occasion={occasion} member={member} />
                                    )}
                                    {canRemoveMember && member.role !== 'host' && occasion.status !== 'completed' && (
                                        <RemoveMemberButton member={member} />
                                    )}
                                </div>
                            </li>
                        ))}
                    </ul>

                    {pendingInvitations.length > 0 && (
                        <>
                            <h2 className="mt-6 text-sm font-medium text-text-primary">Pending invitations</h2>
                            <ul className="mt-3 divide-y divide-border rounded-lg border border-border bg-surface">
                                {pendingInvitations.map((invitation) => (
                                    <li key={invitation.id} className="flex items-center justify-between px-4 py-3">
                                        <div>
                                            <p className="text-sm text-text-primary">{invitation.email}</p>
                                            <p className="text-xs text-text-secondary">{roleLabel(roles, invitation.role)}</p>
                                        </div>
                                        <span className="text-xs text-text-secondary">
                                            Expires {new Date(invitation.expires_at).toLocaleDateString()}
                                        </span>
                                    </li>
                                ))}
                            </ul>
                        </>
                    )}
                </div>

                {canInvite && (
                    <div>
                        <h2 className="text-sm font-medium text-text-primary">Invite someone</h2>
                        <Form
                            action={route('occasions.committee.invite', occasion.slug)}
                            method="post"
                            resetOnSuccess
                            className="mt-3 space-y-3"
                        >
                            {({ errors, processing, wasSuccessful }) => (
                                <>
                                    <FormField label="Email" htmlFor="email" required error={errors.email}>
                                        <Input id="email" name="email" type="email" required invalid={!!errors.email} />
                                    </FormField>

                                    <FormField
                                        label="Role"
                                        htmlFor="role"
                                        required
                                        error={errors.role}
                                        helperText="The Role determines what this member can do — see the Committee Role guide."
                                    >
                                        <Select id="role" name="role" required defaultValue="" invalid={!!errors.role}>
                                            <option value="" disabled>
                                                Select a role
                                            </option>
                                            {roles.map((role) => (
                                                <option key={role.value} value={role.value}>
                                                    {role.label}
                                                </option>
                                            ))}
                                        </Select>
                                    </FormField>

                                    <FormField label="Notes (optional)" htmlFor="notes">
                                        <Textarea id="notes" name="notes" rows={2} placeholder="e.g. helping with catering" />
                                    </FormField>

                                    <Button type="submit" loading={processing}>
                                        {processing ? 'Sending…' : 'Send invitation'}
                                    </Button>

                                    {wasSuccessful && <Alert variant="success">Invitation sent.</Alert>}
                                </>
                            )}
                        </Form>
                    </div>
                )}
            </div>
        </OccasionWorkspaceLayout>
    );
}
