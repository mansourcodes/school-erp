const MAX_SIDE = 1024;

/**
 * Phone photos are 3–8 MB; the server accepts ≤ 2 MB and re-encodes to 512 px anyway.
 * Shrink on the device first (less mobile data, always under the limit). `createImageBitmap`
 * applies the EXIF rotation, so the photo is upright. Falls back to the original file.
 */
export async function resizeImage(file: File): Promise<Blob> {
  if (typeof createImageBitmap !== 'function') return file;

  try {
    const bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' });
    const ratio = Math.min(1, MAX_SIDE / Math.max(bitmap.width, bitmap.height));
    const canvas = document.createElement('canvas');
    canvas.width = Math.round(bitmap.width * ratio);
    canvas.height = Math.round(bitmap.height * ratio);
    const context = canvas.getContext('2d');
    if (!context) return file;
    context.drawImage(bitmap, 0, 0, canvas.width, canvas.height);
    bitmap.close();

    const blob = await new Promise<Blob | null>((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.85));
    return blob ?? file;
  } catch {
    return file;
  }
}
