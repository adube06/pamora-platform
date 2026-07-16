import { Form, useForm } from '@inertiajs/react';
import { useState } from 'react';
import Badge from '@/Components/Badge';
import Button from '@/Components/Button';
import Card from '@/Components/Card';
import EmptyState from '@/Components/EmptyState';
import FormField from '@/Components/FormField';
import Input from '@/Components/Input';
import Select from '@/Components/Select';
import OccasionWorkspaceLayout from '@/Layouts/OccasionWorkspaceLayout';
import type { Album, MediaAsset, Occasion } from '@/types/models';

interface Props {
    occasion: Occasion;
    mediaAssets: MediaAsset[];
    albums: Album[];
    canUploadMedia: boolean;
    canEditMediaMetadata: boolean;
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

function AlbumSelect({ asset, albums }: { asset: MediaAsset; albums: Album[] }) {
    const { data, setData, patch, processing } = useForm({
        album_id: asset.album?.id ?? '',
    });

    function handleChange(e: React.ChangeEvent<HTMLSelectElement>) {
        setData('album_id', e.target.value);
        patch(route('media.move', asset.id), { preserveScroll: true });
    }

    return (
        <Select value={data.album_id} onChange={handleChange} disabled={processing} className="mt-2 px-2 py-1 text-xs">
            <option value="">Ungrouped</option>
            {albums.map((album) => (
                <option key={album.id} value={album.id}>
                    {album.name}
                </option>
            ))}
        </Select>
    );
}

function MediaCard({ asset, albums, canEditMediaMetadata }: { asset: MediaAsset; albums: Album[]; canEditMediaMetadata: boolean }) {
    return (
        <Card className="h-full">
            <a href={asset.download_url} className="block">
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
            </a>
            {asset.visibility === 'private' && (
                <Badge variant="warning" className="mt-2">
                    Private
                </Badge>
            )}
            {canEditMediaMetadata && albums.length > 0 && <AlbumSelect asset={asset} albums={albums} />}
        </Card>
    );
}

export default function Media({ occasion, mediaAssets, albums, canUploadMedia, canEditMediaMetadata }: Props) {
    const [showUploadForm, setShowUploadForm] = useState(false);
    const [showAlbumForm, setShowAlbumForm] = useState(false);

    const ungroupedAssets = mediaAssets.filter((asset) => asset.album === null);

    return (
        <OccasionWorkspaceLayout occasion={occasion} active="media">
            <div className="flex items-center justify-between">
                <h2 className="text-sm font-medium text-text-primary">Media</h2>
                <div className="flex gap-2">
                    {canUploadMedia && (
                        <Button variant="ghost" size="sm" onClick={() => setShowAlbumForm((v) => !v)}>
                            {showAlbumForm ? 'Cancel' : 'New Album'}
                        </Button>
                    )}
                    {canUploadMedia && (
                        <Button size="sm" onClick={() => setShowUploadForm((v) => !v)}>
                            {showUploadForm ? 'Cancel' : 'Upload File'}
                        </Button>
                    )}
                </div>
            </div>

            {showAlbumForm && (
                <Card className="mt-4 max-w-md">
                    <Form
                        action={route('occasions.albums.store', occasion.slug)}
                        method="post"
                        resetOnSuccess
                        onSuccess={() => setShowAlbumForm(false)}
                        className="space-y-3"
                    >
                        {({ errors, processing }) => (
                            <>
                                <FormField label="Album Name" htmlFor="album_name" required error={errors.name}>
                                    <Input
                                        id="album_name"
                                        name="name"
                                        type="text"
                                        required
                                        placeholder="e.g. Ceremony"
                                        invalid={!!errors.name}
                                    />
                                </FormField>

                                <Button type="submit" loading={processing}>
                                    {processing ? 'Creating…' : 'Create Album'}
                                </Button>
                            </>
                        )}
                    </Form>
                </Card>
            )}

            {showUploadForm && (
                <Card className="mt-4 max-w-md">
                    <Form
                        action={route('occasions.media.store', occasion.slug)}
                        method="post"
                        encType="multipart/form-data"
                        resetOnSuccess
                        onSuccess={() => setShowUploadForm(false)}
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
                <div className="mt-4 space-y-6">
                    {albums.map((album) => {
                        const albumAssets = mediaAssets.filter((asset) => asset.album?.id === album.uuid);

                        if (albumAssets.length === 0) {
                            return null;
                        }

                        return (
                            <div key={album.id}>
                                <h3 className="mb-2 text-xs font-medium tracking-wide text-text-secondary uppercase">{album.name}</h3>
                                <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                                    {albumAssets.map((asset) => (
                                        <MediaCard key={asset.id} asset={asset} albums={albums} canEditMediaMetadata={canEditMediaMetadata} />
                                    ))}
                                </div>
                            </div>
                        );
                    })}

                    {ungroupedAssets.length > 0 && (
                        <div>
                            {albums.length > 0 && (
                                <h3 className="mb-2 text-xs font-medium tracking-wide text-text-secondary uppercase">Ungrouped</h3>
                            )}
                            <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                                {ungroupedAssets.map((asset) => (
                                    <MediaCard key={asset.id} asset={asset} albums={albums} canEditMediaMetadata={canEditMediaMetadata} />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            )}
        </OccasionWorkspaceLayout>
    );
}
