import { ArchPhoto } from '@/shared/ui';
import { type Viewer, viewerInitial } from '../model/types';

interface ViewerAvatarProps {
  viewer: Viewer;
  className?: string;
  textClassName?: string;
}

export function ViewerAvatar({ viewer, className, textClassName }: ViewerAvatarProps) {
  return (
    <ArchPhoto
      className={className}
      textClassName={textClassName}
      src={viewer.photo_url}
      alt={viewer.name}
      initial={viewerInitial(viewer)}
    />
  );
}
