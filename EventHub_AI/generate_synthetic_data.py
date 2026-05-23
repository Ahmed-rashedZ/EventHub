import pandas as pd
import numpy as np
import os

# Paths
base_dir = r"c:\Users\Ahmed\Desktop\EventHub\EventHub_AI"
original_path = os.path.join(base_dir, "events_dataset.csv")
synthetic_path = os.path.join(base_dir, "synthetic_events_dataset.csv")

if not os.path.exists(original_path):
    print("Original dataset not found.")
    exit(1)

# Load original data
df_orig = pd.read_csv(original_path)

# Calculate statistics per event type
stats = {}
event_types = df_orig['Event_Type'].unique()

for t in event_types:
    sub = df_orig[df_orig['Event_Type'] == t]
    stats[t] = {
        'mean_attendance': sub['Actual_Attendance'].mean(),
        'std_attendance': max(10, sub['Actual_Attendance'].std() if len(sub) > 1 else sub['Actual_Attendance'].mean() * 0.2),
        'min_days': int(sub['Total_Days'].min()),
        'max_days': int(sub['Total_Days'].max()),
        'weekend_ratio': sub['Includes_Weekend'].mean(),
        'morning_ratio': (sub['Time_Period'] == 'Morning').mean()
    }

# Generate 500 synthetic rows
np.random.seed(42)
synthetic_rows = []

for _ in range(500):
    # 1. Choose Event Type based on original distribution
    t = np.random.choice(event_types)
    t_stats = stats[t]
    
    # 2. Generate Total_Days realistically for this event type
    days = int(np.random.randint(t_stats['min_days'], t_stats['max_days'] + 1))
    
    # 3. Generate Includes_Weekend based on probability
    includes_weekend = int(np.random.rand() < t_stats['weekend_ratio'])
    
    # 4. Generate Time_Period based on probability
    time_period = 'Morning' if np.random.rand() < t_stats['morning_ratio'] else 'Evening'
    
    # 5. Generate Actual_Attendance based on normal distribution of this event type
    # Add a duration factor (+10% per day above min) and weekend factor (-15% if weekend is included for professional events)
    base_att = np.random.normal(t_stats['mean_attendance'], t_stats['std_attendance'])
    
    # Apply logical constraints/multipliers
    duration_mult = 1.0 + (days - t_stats['min_days']) * 0.1
    weekend_mult = 0.85 if (includes_weekend and t in ['Meeting', 'Seminar', 'Workshop', 'Course']) else 1.15 if (includes_weekend and t in ['Festival', 'Entertainment']) else 1.0
    time_mult = 1.1 if time_period == 'Morning' else 0.9
    
    attendance = max(10, int(base_att * duration_mult * weekend_mult * time_mult))
    
    synthetic_rows.append({
        'Event_Type': t,
        'Total_Days': days,
        'Includes_Weekend': includes_weekend,
        'Time_Period': time_period,
        'Actual_Attendance': attendance
    })

# Create DataFrame
df_synth = pd.DataFrame(synthetic_rows)

# Concatenate with original data to preserve original data points as anchors
df_final = pd.concat([df_orig, df_synth], ignore_index=True)

# Save to file
df_final.to_csv(synthetic_path, index=False)
print(f"Generated synthetic dataset with {len(df_final)} rows saved to {synthetic_path}")
