import { Bell, BellOff, BellRing, CircleCheck, Info } from 'lucide-react';
import { useState } from 'react';
import { t } from '@/shared/i18n';
import { pushPermission, pushSupport, subscribeThisDevice } from '@/shared/lib';
import {
  type NotificationSettings,
  useNotificationSettingsQuery,
  usePushConfigQuery,
  useSubscribePushMutation,
  useUpdateNotificationSettingsMutation,
} from '../api/push-api';

/** Enables push on this device and subscribes it; shared by the profile card and the soft prompt. */
export function useEnablePush() {
  const { data: config } = usePushConfigQuery();
  const [subscribe] = useSubscribePushMutation();
  const [permission, setPermission] = useState<NotificationPermission>(pushPermission());
  const [busy, setBusy] = useState(false);

  const enable = async () => {
    if (!config?.public_key) return;
    setBusy(true);
    try {
      const subscription = await subscribeThisDevice(config.public_key);
      setPermission(pushPermission());
      if (subscription) await subscribe(subscription.toJSON()).unwrap();
    } finally {
      setBusy(false);
    }
  };

  return { enabled: !!config?.enabled, permission, busy, enable, support: pushSupport() };
}

/** حسابي → الإشعارات (PLAN.md §3.6). */
export function NotificationsCard() {
  const push = useEnablePush();
  const { data: settings } = useNotificationSettingsQuery();
  const [update] = useUpdateNotificationSettingsMutation();

  if (!push.enabled) return null;

  const toggle = (type: keyof NotificationSettings) => settings && void update({ [type]: !settings[type] });

  return (
    <section className="card-soft p-5 sm:p-6" aria-labelledby="notifications-title">
      <h3 id="notifications-title" className="font-display text-lg mb-3 flex items-center gap-2">
        <Bell className="size-5 text-primary" strokeWidth={1.75} aria-hidden="true" />
        {t('notifications.title')}
      </h3>

      {push.support === 'ios-needs-install' ? (
        <div role="note" className="alert alert-soft text-sm">
          <Info className="size-5" strokeWidth={1.75} aria-hidden="true" />
          <span>{t('notifications.iosInstall')}</span>
        </div>
      ) : push.support === 'unsupported' ? (
        <p className="text-sm opacity-70">{t('notifications.unsupported')}</p>
      ) : push.permission === 'granted' ? (
        <p className="text-sm text-success flex items-center gap-2">
          <CircleCheck className="size-4" strokeWidth={1.75} aria-hidden="true" />
          {t('notifications.granted')}
        </p>
      ) : push.permission === 'denied' ? (
        <div role="alert" className="alert alert-soft alert-warning text-sm">
          <BellOff className="size-5" strokeWidth={1.75} aria-hidden="true" />
          <span>{t('notifications.denied')}</span>
        </div>
      ) : (
        <div className="rounded-box bg-primary/10 border border-primary/20 p-4 flex flex-col sm:flex-row sm:items-center gap-3">
          <div className="flex-1">
            <p className="font-semibold">{t('notifications.enableTitle')}</p>
            <p className="text-sm opacity-80">{t('notifications.enableBody')}</p>
          </div>
          <button type="button" className="btn btn-primary w-full sm:w-auto" disabled={push.busy} onClick={() => void push.enable()}>
            <BellRing className="size-4" strokeWidth={1.75} aria-hidden="true" />
            {t('notifications.enable')}
          </button>
        </div>
      )}

      {settings && (
        <ul className="divide-y divide-base-300 mt-3">
          {(['attendance', 'messages'] as const).map((type) => (
            <li key={type} className="py-3 flex items-center gap-3">
              <div className="flex-1">
                <p className="font-medium">{t(`notifications.${type}`)}</p>
                <p className="text-xs opacity-70">{t(`notifications.${type}Hint`)}</p>
              </div>
              <input
                type="checkbox"
                className="toggle toggle-primary"
                checked={settings[type]}
                aria-label={t(`notifications.${type}`)}
                onChange={() => toggle(type)}
              />
            </li>
          ))}
        </ul>
      )}
      <p className="text-xs opacity-60 mt-2 flex items-start gap-1.5">
        <Info className="size-3.5 mt-0.5 shrink-0" strokeWidth={1.75} aria-hidden="true" />
        {t('notifications.iosNote')}
      </p>
    </section>
  );
}
