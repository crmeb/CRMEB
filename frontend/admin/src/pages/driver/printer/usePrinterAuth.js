import { includeArray } from '@/libs/auth';
import { useUserInfoStore } from '@/store/module/userInfo';

/**
 * @description 打印页面权限判断，未加载权限时默认拒绝普通管理员
 */
export function usePrinterAuth() {
  const userInfoStore = useUserInfoStore();

  function hasPermission(auth) {
    const level = userInfoStore.userInfo?.level;
    if (level === 0 || level === '0') return true;
    return Array.isArray(userInfoStore.access) && includeArray([auth], userInfoStore.access);
  }

  return { hasPermission };
}
