/**
 * Geometry for the photo cropper: the image is "cover"-fitted into a fixed frame, then the
 * user zooms (≥ 1), rotates by quarter turns and drags. Offsets are in frame pixels from the
 * centre and always clamped so the image covers the whole frame (no empty corners).
 */

export interface Size {
  width: number;
  height: number;
}

export interface CropState {
  /** Quarter turns clockwise, 0–3. */
  turns: number;
  /** 1 = the image just covers the frame. */
  zoom: number;
  x: number;
  y: number;
}

export const MIN_ZOOM = 1;
export const MAX_ZOOM = 4;

/** The image's on-screen scale (frame px per image px) for the given state. */
export function cropScale(image: Size, frame: Size, turns: number, zoom: number): number {
  const odd = turns % 2 === 1;
  const width = odd ? image.height : image.width;
  const height = odd ? image.width : image.height;
  return Math.max(frame.width / width, frame.height / height) * zoom;
}

/** Keeps the offset inside the range where the (rotated, scaled) image still covers the frame. */
export function clampCrop(image: Size, frame: Size, state: CropState): CropState {
  const zoom = Math.min(MAX_ZOOM, Math.max(MIN_ZOOM, state.zoom));
  const scale = cropScale(image, frame, state.turns, zoom);
  const odd = state.turns % 2 === 1;
  const shownWidth = (odd ? image.height : image.width) * scale;
  const shownHeight = (odd ? image.width : image.height) * scale;
  const maxX = Math.max(0, (shownWidth - frame.width) / 2);
  const maxY = Math.max(0, (shownHeight - frame.height) / 2);
  return {
    turns: ((state.turns % 4) + 4) % 4,
    zoom,
    x: Math.min(maxX, Math.max(-maxX, state.x)),
    y: Math.min(maxY, Math.max(-maxY, state.y)),
  };
}

/**
 * Draws exactly what the frame shows onto a canvas of `output` size and returns a JPEG.
 * Resolves null when canvas is unavailable (very old browsers, jsdom).
 */
export async function renderCrop(
  source: CanvasImageSource,
  image: Size,
  frame: Size,
  state: CropState,
  output: Size,
): Promise<Blob | null> {
  const canvas = document.createElement('canvas');
  canvas.width = output.width;
  canvas.height = output.height;
  let context: CanvasRenderingContext2D | null = null;
  try {
    context = canvas.getContext('2d');
  } catch {
    return null;
  }
  if (!context) return null;

  const k = output.width / frame.width;
  const scale = cropScale(image, frame, state.turns, state.zoom) * k;
  context.fillStyle = '#ffffff';
  context.fillRect(0, 0, output.width, output.height);
  context.imageSmoothingQuality = 'high';
  context.translate(output.width / 2 + state.x * k, output.height / 2 + state.y * k);
  context.rotate((state.turns * Math.PI) / 2);
  const width = image.width * scale;
  const height = image.height * scale;
  context.drawImage(source, -width / 2, -height / 2, width, height);

  return new Promise<Blob | null>((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.9));
}
