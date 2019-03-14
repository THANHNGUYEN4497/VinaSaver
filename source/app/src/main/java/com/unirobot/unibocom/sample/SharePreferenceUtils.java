package com.unirobot.unibocom.sample;

import android.app.Activity;
import android.content.Context;
import android.content.SharedPreferences;

/**
 * Created by HungPhan on 20/03/2018.
 * Copyright © Saver Corp 2018.
 */

public class SharePreferenceUtils {
    private static SharePreferenceUtils s_sharePreferenceUtils;
    private static SharedPreferences s_sharePreferences;
    public static final String ROOM_VALUE = "room_name";
    public static final String USER_VALUE = "user_name";
    public static final String LINK_VALUE = "sever_link";
    public static final String UUID_VALUE = "uuid";

    private SharePreferenceUtils(Activity activity) {
        if (s_sharePreferences != null) {
            return;
        }
        s_sharePreferences = activity.getPreferences(Context.MODE_PRIVATE);
    }

    public static synchronized SharePreferenceUtils getInstance(Activity activity) {
        if (s_sharePreferences == null) {
            s_sharePreferenceUtils = new SharePreferenceUtils(activity);
        }
        return s_sharePreferenceUtils;
    }

    public boolean saveStringValue(String value, String key) {
        if (s_sharePreferences == null) {
            return false;
        }
        SharedPreferences.Editor editor = s_sharePreferences.edit();
        editor.putString(key, value);
        return editor.commit();
    }

    public boolean saveIntValue(int value, String key) {
        if (s_sharePreferences == null) {
            return false;
        }
        SharedPreferences.Editor editor = s_sharePreferences.edit();
        editor.putInt(key, value);
        return editor.commit();
    }

    public String getStringValue(String key) {
        if (s_sharePreferences == null) {
            return null;
        }
        return s_sharePreferences.getString(key, null);
    }

    public int getIntValue(String key) {
        if (s_sharePreferences == null) {
            return 0;
        }
        return s_sharePreferences.getInt(key, 0);
    }

    public void clearSharePreferenceValue() {
        SharedPreferences.Editor editor = s_sharePreferences.edit();
        editor.clear();
        editor.apply();
    }
}
