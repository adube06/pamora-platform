import { Form } from '@inertiajs/react';
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
                    <h2 className="text-sm font-medium text-gray-900">Members</h2>
                    <ul className="mt-3 divide-y divide-gray-200 rounded-md border border-gray-200 bg-white">
                        {members.map((member) => (
                            <li key={member.id} className="flex items-center justify-between px-4 py-3">
                                <div>
                                    <p className="text-sm font-medium text-gray-900">{member.user?.name}</p>
                                    <p className="text-xs text-gray-500">{member.user?.email}</p>
                                    {member.notes && <p className="mt-0.5 text-xs text-gray-400">{member.notes}</p>}
                                </div>
                                <span className="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                                    {member.role === 'host' ? 'Host' : roleLabel(roles, member.role)}
                                </span>
                            </li>
                        ))}
                    </ul>

                    {pendingInvitations.length > 0 && (
                        <>
                            <h2 className="mt-6 text-sm font-medium text-gray-900">Pending invitations</h2>
                            <ul className="mt-3 divide-y divide-gray-200 rounded-md border border-gray-200 bg-white">
                                {pendingInvitations.map((invitation) => (
                                    <li key={invitation.id} className="flex items-center justify-between px-4 py-3">
                                        <div>
                                            <p className="text-sm text-gray-900">{invitation.email}</p>
                                            <p className="text-xs text-gray-500">{roleLabel(roles, invitation.role)}</p>
                                        </div>
                                        <span className="text-xs text-gray-500">
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
                        <h2 className="text-sm font-medium text-gray-900">Invite someone</h2>
                        <Form
                            action={route('occasions.committee.invite', occasion.slug)}
                            method="post"
                            resetOnSuccess
                            className="mt-3 space-y-3"
                        >
                            {({ errors, processing, wasSuccessful }) => (
                                <>
                                    <div>
                                        <label htmlFor="email" className="block text-sm font-medium text-gray-700">
                                            Email
                                        </label>
                                        <input
                                            id="email"
                                            name="email"
                                            type="email"
                                            required
                                            className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                                        />
                                        {errors.email && <p className="mt-1 text-sm text-red-600">{errors.email}</p>}
                                    </div>

                                    <div>
                                        <label htmlFor="role" className="block text-sm font-medium text-gray-700">
                                            Role
                                        </label>
                                        <select
                                            id="role"
                                            name="role"
                                            required
                                            defaultValue=""
                                            className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                                        >
                                            <option value="" disabled>
                                                Select a role
                                            </option>
                                            {roles.map((role) => (
                                                <option key={role.value} value={role.value}>
                                                    {role.label}
                                                </option>
                                            ))}
                                        </select>
                                        {errors.role && <p className="mt-1 text-sm text-red-600">{errors.role}</p>}
                                        <p className="mt-1 text-xs text-gray-500">
                                            The Role determines what this member can do — see the Committee Role guide.
                                        </p>
                                    </div>

                                    <div>
                                        <label htmlFor="notes" className="block text-sm font-medium text-gray-700">
                                            Notes (optional)
                                        </label>
                                        <textarea
                                            id="notes"
                                            name="notes"
                                            rows={2}
                                            placeholder="e.g. helping with catering"
                                            className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                                        />
                                    </div>

                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                                    >
                                        {processing ? 'Sending…' : 'Send invitation'}
                                    </button>

                                    {wasSuccessful && <p className="text-sm text-green-700">Invitation sent.</p>}
                                </>
                            )}
                        </Form>
                    </div>
                )}
            </div>
        </OccasionWorkspaceLayout>
    );
}
