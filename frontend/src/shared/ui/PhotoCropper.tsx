import { RotateCcw, RotateCw, Undo2, ZoomIn, ZoomOut } from 'lucide-react';
import { type KeyboardEvent, type PointerEvent, useEffect, useRef, useState } from 'react';
import { t } from '@/shared/i18n';
import { clampCrop, cropScale, type CropState, MAX_ZOOM, MIN_ZOOM, renderCrop, resizeImage, type Size } from '@/shared/lib';
import { Dialog } from './Dialog';

/** The arch photo ratio (100:128), shown on cards and the student ID. */
const FRAME: Size = { width: 250, height: 320 };
/** Stored at 400×512; the server re-encodes to ≤ 512 px anyway. */
const OUTPUT: Size = { width: 400, height: 512 };
const START: CropState = { turns: 0, zoom: 1, x: 0, y: 0 };
const ARCH = 'M0 128V42Q0 28 12 20Q30 8 50 0Q70 8 88 20Q100 28 100 42V128Z';

interface PhotoCropperProps {
  /** The picked photo; the sheet is open while it is set. */
  file: File | null;
  busy: boolean;
  onCancel: () => void;
  onConfirm: (photo: Blob) => void;
}

/**
 * Preview a picked photo of any size, then drag / pinch / zoom / rotate it into the arch frame
 * before it is uploaded. Only the framed part is sent, as a small JPEG.
 */
export function PhotoCropper({ file, busy, onCancel, onConfirm }: PhotoCropperProps) {
  const [url, setUrl] = useState<string | null>(null);

  useEffect(() => {
    if (!file) {
      setUrl(null);
      return;
    }
    const next = URL.createObjectURL(file);
    setUrl(next);
    return () => URL.revokeObjectURL(next);
  }, [file]);

  return (
    <Dialog open={file !== null} onClose={onCancel} labelledBy="photo-crop-title" closeLabel={t('photoCrop.cancel')}>
      {file && url && <CropperBody key={url} file={file} url={url} busy={busy} onCancel={onCancel} onConfirm={onConfirm} />}
    </Dialog>
  );
}

interface BodyProps {
  file: File;
  url: string;
  busy: boolean;
  onCancel: () => void;
  onConfirm: (photo: Blob) => void;
}

function CropperBody({ file, url, busy, onCancel, onConfirm }: BodyProps) {
  const img = useRef<HTMLImageElement>(null);
  const frameRef = useRef<HTMLDivElement>(null);
  const pointers = useRef(new Map<number, { x: number; y: number }>());
  const [natural, setNatural] = useState<Size | null>(null);
  const [failed, setFailed] = useState(false);
  const [crop, setCrop] = useState<CropState>(START);
  const [rendering, setRendering] = useState(false);

  const update = (change: (current: CropState) => CropState) =>
    setCrop((current) => (natural ? clampCrop(natural, FRAME, change(current)) : current));

  // Wheel zoom needs a non-passive listener to keep the page from scrolling.
  useEffect(() => {
    const frame = frameRef.current;
    if (!frame) return;
    const onWheel = (event: WheelEvent) => {
      event.preventDefault();
      update((c) => ({ ...c, zoom: c.zoom * (event.deltaY < 0 ? 1.08 : 1 / 1.08) }));
    };
    frame.addEventListener('wheel', onWheel, { passive: false });
    return () => frame.removeEventListener('wheel', onWheel);
  });

  const onPointerDown = (event: PointerEvent<HTMLDivElement>) => {
    event.currentTarget.setPointerCapture(event.pointerId);
    pointers.current.set(event.pointerId, { x: event.clientX, y: event.clientY });
  };

  const onPointerMove = (event: PointerEvent<HTMLDivElement>) => {
    const previous = pointers.current.get(event.pointerId);
    if (!previous) return;
    const all = [...pointers.current.values()];
    if (all.length === 1) {
      const dx = event.clientX - previous.x;
      const dy = event.clientY - previous.y;
      update((c) => ({ ...c, x: c.x + dx, y: c.y + dy }));
    } else if (all.length === 2) {
      const other = [...pointers.current.entries()].find(([id]) => id !== event.pointerId)?.[1];
      if (other) {
        const before = Math.hypot(previous.x - other.x, previous.y - other.y);
        const after = Math.hypot(event.clientX - other.x, event.clientY - other.y);
        if (before > 0) update((c) => ({ ...c, zoom: (c.zoom * after) / before }));
      }
    }
    pointers.current.set(event.pointerId, { x: event.clientX, y: event.clientY });
  };

  const onPointerUp = (event: PointerEvent<HTMLDivElement>) => {
    pointers.current.delete(event.pointerId);
  };

  const onKeyDown = (event: KeyboardEvent<HTMLDivElement>) => {
    const moves: Record<string, [number, number]> = { ArrowLeft: [-10, 0], ArrowRight: [10, 0], ArrowUp: [0, -10], ArrowDown: [0, 10] };
    const move = moves[event.key];
    if (move) {
      event.preventDefault();
      update((c) => ({ ...c, x: c.x + move[0], y: c.y + move[1] }));
    } else if (event.key === '+' || event.key === '=') {
      update((c) => ({ ...c, zoom: c.zoom * 1.1 }));
    } else if (event.key === '-') {
      update((c) => ({ ...c, zoom: c.zoom / 1.1 }));
    }
  };

  const confirm = async () => {
    if (!natural || !img.current) return;
    setRendering(true);
    const blob = (await renderCrop(img.current, natural, FRAME, crop, OUTPUT)) ?? (await resizeImage(file));
    setRendering(false);
    onConfirm(blob);
  };

  const scale = natural ? cropScale(natural, FRAME, crop.turns, crop.zoom) : 0;
  const working = busy || rendering;

  return (
    <div className="p-6 pb-[calc(1.5rem+env(safe-area-inset-bottom))]">
      <div className="sheet-grip" />
      <h2 id="photo-crop-title" className="font-display text-lg">
        {t('photoCrop.title')}
      </h2>
      <p className="text-sm opacity-75 mt-1">{failed ? t('photoCrop.unreadable') : t('photoCrop.hint')}</p>

      <div
        ref={frameRef}
        className="relative mx-auto mt-4 overflow-hidden rounded-box bg-base-300 touch-none select-none cursor-grab active:cursor-grabbing focus-visible:outline-2 focus-visible:outline-primary"
        style={{ width: FRAME.width, height: FRAME.height }}
        tabIndex={0}
        role="group"
        aria-label={t('photoCrop.frame')}
        data-testid="photo-crop-frame"
        onPointerDown={onPointerDown}
        onPointerMove={onPointerMove}
        onPointerUp={onPointerUp}
        onPointerCancel={onPointerUp}
        onKeyDown={onKeyDown}
      >
        {!failed && (
          <img
            ref={img}
            src={url}
            alt=""
            draggable={false}
            className="absolute max-w-none pointer-events-none"
            style={
              natural
                ? {
                    left: '50%',
                    top: '50%',
                    width: natural.width * scale,
                    height: natural.height * scale,
                    transform: `translate(-50%, -50%) translate(${crop.x}px, ${crop.y}px) rotate(${crop.turns * 90}deg)`,
                  }
                : { opacity: 0 }
            }
            onLoad={(event) => {
              const { naturalWidth: width, naturalHeight: height } = event.currentTarget;
              if (width > 0 && height > 0) setNatural({ width, height });
              else setFailed(true);
            }}
            onError={() => setFailed(true)}
          />
        )}
        {!natural && !failed && (
          <span className="loading loading-spinner loading-md absolute inset-0 m-auto" aria-hidden="true" />
        )}
        {/* Dim what the arch frame cuts off, then outline the arch. */}
        <svg className="absolute inset-0 size-full pointer-events-none" viewBox="0 0 100 128" preserveAspectRatio="none" aria-hidden="true">
          <path d={`M0 0H100V128H0Z ${ARCH}`} fillRule="evenodd" fill="rgb(0 0 0 / 0.55)" />
          <path d={ARCH} fill="none" stroke="white" strokeOpacity="0.85" strokeWidth="1.5" vectorEffect="non-scaling-stroke" />
        </svg>
      </div>

      <div className="mt-4 flex items-center gap-2" dir="ltr">
        <ZoomOut className="size-4 opacity-70 shrink-0" strokeWidth={1.75} aria-hidden="true" />
        <input
          type="range"
          className="range range-primary range-xs flex-1"
          min={MIN_ZOOM}
          max={MAX_ZOOM}
          step={0.01}
          value={crop.zoom}
          disabled={!natural}
          aria-label={t('photoCrop.zoom')}
          onChange={(e) => update((c) => ({ ...c, zoom: Number(e.target.value) }))}
        />
        <ZoomIn className="size-4 opacity-70 shrink-0" strokeWidth={1.75} aria-hidden="true" />
      </div>

      <div className="mt-3 grid grid-cols-3 gap-2">
        <button type="button" className="btn btn-sm" disabled={!natural} onClick={() => update((c) => ({ ...c, turns: c.turns + 3, x: 0, y: 0 }))}>
          <RotateCcw className="size-4" strokeWidth={1.75} aria-hidden="true" />
          {t('photoCrop.rotateLeft')}
        </button>
        <button type="button" className="btn btn-sm" disabled={!natural} onClick={() => update((c) => ({ ...c, turns: c.turns + 1, x: 0, y: 0 }))}>
          <RotateCw className="size-4" strokeWidth={1.75} aria-hidden="true" />
          {t('photoCrop.rotateRight')}
        </button>
        <button type="button" className="btn btn-sm btn-ghost" disabled={!natural} onClick={() => setCrop(START)}>
          <Undo2 className="size-4" strokeWidth={1.75} aria-hidden="true" />
          {t('photoCrop.reset')}
        </button>
      </div>

      <div className="grid grid-cols-2 gap-2 mt-6">
        <button type="button" className="btn btn-lg" disabled={working} onClick={onCancel}>
          {t('photoCrop.cancel')}
        </button>
        <button type="button" className="btn btn-primary btn-lg" disabled={!natural || working} onClick={() => void confirm()}>
          {working && <span className="loading loading-spinner loading-sm" aria-hidden="true" />}
          {t('photoCrop.save')}
        </button>
      </div>
    </div>
  );
}
