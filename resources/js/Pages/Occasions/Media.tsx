import { Form } from '@inertiajs/react';
import { useState } from 'react';
import Badge from '@/Components/Badge';
import Button from '@/Components/Button';
import Card from '@/Components/Card';
import EmptyState from '@/Components/EmptyState';
import FormField from '@/Components/FormField';
import Input from '@/Components/Input';
import Select from '@/Components/Select';
import OccasionWorkspaceLayout from '@/Layouts/OccasionWorkspaceLayout';
import type { MediaAsset, Occasion } from '@/types/models';

interface Props {
    occasion: Occasion;
    mediaAssets: MediaAsset[];
    canUploadMedia: boolean;
}

function formatSize(bytes: number): string {
    if (bytes < 1024) {
        return `${bytes} B`;
    }

    if (bytes < 1024 * 1024) {
        return `${(bytes / 1024).toFixed(1)} KB`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

export default function Media({ occasion, mediaAssets, canUploadMedia }: Props) {
    const [showForm, setShowForm] = useState(false);

    return (
        <OccasionWorkspaceLayout occasion={occasion} active="media">
            <div className="flex items-center justify-between">
                <h2 className="text-sm font-medium text-text-primary">Media</h2>
                {canUploadMedia && (
                    <Button size="sm" onClick={() => setShowForm((v) => !v)}>
                        {showForm ? 'Cancel' : 'Upload File'}
                    </Button>
                )}
            </div>

            {showForm && (
                <Card className="mt-4 max-w-md">
                    <Form
                        action={route('occasions.media.store', occasion.slug)}
                        method="post"
                        encType="multipart/form-data"
                        resetOnSuccess
                        onSuccess={() => setShowForm(false)}
                        className="space-y-3"
                    >
                        {({ errors, processing }) => (
                            <>
                                <FormField label="File" htmlFor="file" required error={errors.file}>
                                    <Input id="file" name="file" type="file" required invalid={!!errors.file} />
                                </FormField>

                                <FormField label="Visibility" htmlFor="visibility">
                                    <Select id="visibility" name="visibility" defaultValue="occasion_members">
                                        <option value="occasion_members">Occasion Members</option>
                                        <option value="private">Private (only me)</option>
                                    </Select>
                                </FormField>

                                <Button type="submit" loading={processing}>
                                    {processing ? 'Uploading…' : 'Upload'}
                                </Button>
                            </>
                        )}
                    </Form>
                </Card>
            )}

            {mediaAssets.length === 0 ? (
                <div className="mt-4">
                    <EmptyState title="No media yet" description="Photos, videos, and documents uploaded here will show up as the Occasion's digital memory." />
                </div>
            ) : (
                <div className="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                    {mediaAssets.map((asset) => (
                        <a key={asset.id} href={asset.download_url} className="block">
                            <Card className="h-full">
                                {asset.file_type === 'image' ? (
                                    <img
                                        src={asset.download_url}
                                        alt={asset.file_name}
                                        className="mb-2 aspect-square w-full rounded-md object-cover"
                                    />
                                ) : (
                                    <div className="mb-2 flex aspect-square w-full items-center justify-center rounded-md bg-background">
                                        <Badge variant="neutral">{asset.file_type.toUpperCase()}</Badge>
                                    </div>
                                )}
                                <p className="truncate text-xs font-medium text-text-primary">{asset.file_name}</p>
                                <p className="mt-0.5 text-xs text-text-secondary">
                                    {formatSize(asset.size)} · {asset.uploaded_by}
                                </p>
                                {asset.visibility === 'private' && (
                                    <Badge variant="warning" className="mt-2">
                                        Private
                                    </Badge>
                                )}
                            </Card>
                        </a>
                    ))}
                </div>
            )}
        </OccasionWorkspaceLayout>
    );
}
