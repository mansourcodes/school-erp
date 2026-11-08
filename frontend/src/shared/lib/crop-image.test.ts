import { clampCrop, cropScale } from './crop-image';

const frame = { width: 250, height: 320 };
// The photo that failed the old 300 px minimum.
const photo = { width: 265, height: 391 };

describe('crop geometry', () => {
  it('cover-fits the image so it fills the frame at zoom 1', () => {
    const scale = cropScale(photo, frame, 0, 1);
    expect(photo.width * scale).toBeGreaterThanOrEqual(frame.width - 0.001);
    expect(photo.height * scale).toBeGreaterThanOrEqual(frame.height - 0.001);
  });

  it('swaps the sides on a quarter turn', () => {
    const wide = { width: 800, height: 400 };
    expect(cropScale(wide, frame, 1, 1)).toBeCloseTo(Math.max(250 / 400, 320 / 800));
  });

  it('never drags the image off an edge of the frame', () => {
    const scale = cropScale(photo, frame, 0, 2);
    const clamped = clampCrop(photo, frame, { turns: 0, zoom: 2, x: 9999, y: -9999 });
    expect(clamped.x).toBeCloseTo((photo.width * scale - frame.width) / 2);
    expect(clamped.y).toBeCloseTo(-(photo.height * scale - frame.height) / 2);
  });

  it('keeps zoom within bounds and turns within 0–3', () => {
    expect(clampCrop(photo, frame, { turns: 5, zoom: 10, x: 0, y: 0 })).toMatchObject({ turns: 1, zoom: 4 });
    expect(clampCrop(photo, frame, { turns: -1, zoom: 0.2, x: 0, y: 0 })).toMatchObject({ turns: 3, zoom: 1 });
  });
});
