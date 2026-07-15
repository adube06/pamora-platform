import { Form } from '@inertiajs/react';
import Alert from '@/Components/Alert';
import Avatar from '@/Components/Avatar';
import Badge from '@/Components/Badge';
import Button from '@/Components/Button';
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
}

function roleLabel(roles: RoleOption[], value: string): string {
    return roles.find((r) => r.value === value)?.label ?? value;
}

export default function Committee({ occasion, members, pendingInvitations, canInvite, roles }: Props) {
    return (
        <OccasionWorkspaceLayout occasion={occasion} active="committee">
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
                                <Badge>{member.role === 'host' ? 'Host' : roleLabel(roles, member.role)}</Badge>
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
