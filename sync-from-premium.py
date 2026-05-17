#!/usr/bin/env python3
"""
Sync ExploreXR plugin from Premium to Free version.

Copies the explorexr plugin folder from ExploreXR-Premium to ExploreXR-Free,
with options to confirm or force overwrite.

Usage:
    python3 sync-from-premium.py                    # Normal sync with confirmation
    python3 sync-from-premium.py --force            # Force overwrite without confirmation
    python3 sync-from-premium.py --dry-run          # Show what would be copied (no changes)
"""

import os
import sys
import shutil
import argparse
from pathlib import Path


def get_paths():
    """Get source and destination paths."""
    source = Path("/mnt/tank/projects/ExploreXR-Premium/wp/wp-content/plugins/explorexr")
    destination = Path("/mnt/tank/projects/ExploreXR-Free/wp/wp-content/plugins/explorexr")
    return source, destination


def validate_paths(source, destination):
    """Validate that source exists and warn if destination exists."""
    if not source.exists():
        print(f"❌ ERROR: Source directory not found: {source}")
        sys.exit(1)
    
    if not source.is_dir():
        print(f"❌ ERROR: Source is not a directory: {source}")
        sys.exit(1)
    
    destination_exists = destination.exists()
    return destination_exists


def count_files(path):
    """Count files in directory tree."""
    count = 0
    for _, _, files in os.walk(path):
        count += len(files)
    return count


def sync_plugin(source, destination, force=False, dry_run=False):
    """Sync plugin from source to destination."""
    destination_exists = destination.exists()
    
    print(f"\n📁 Source:      {source}")
    print(f"📁 Destination: {destination}")
    
    # Count files in source
    file_count = count_files(source)
    print(f"📊 Files to copy: {file_count}")
    
    if destination_exists:
        print(f"\n⚠️  Destination folder already exists.")
        if not force:
            confirm = input("❓ Overwrite? (yes/no): ").strip().lower()
            if confirm not in ("yes", "y"):
                print("❌ Sync cancelled.")
                return False
    
    if dry_run:
        print("\n🔍 DRY RUN MODE - No changes made.")
        print(f"✓ Would copy {file_count} files from {source} to {destination}")
        if destination_exists:
            print(f"✓ Would overwrite existing directory at {destination}")
        return True
    
    try:
        if destination_exists:
            print(f"\n🗑️  Removing existing destination: {destination}")
            shutil.rmtree(destination)
        
        print(f"\n📋 Syncing plugin...")
        shutil.copytree(source, destination)
        
        print(f"✅ Sync complete! Copied {file_count} files.")
        print(f"📁 Destination: {destination}")
        return True
    
    except Exception as e:
        print(f"\n❌ ERROR during sync: {e}")
        sys.exit(1)


def main():
    """Main entry point."""
    parser = argparse.ArgumentParser(
        description="Sync ExploreXR plugin from Premium to Free version."
    )
    parser.add_argument(
        "--force",
        action="store_true",
        help="Force overwrite without confirmation"
    )
    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="Show what would be copied without making changes"
    )
    
    args = parser.parse_args()
    
    source, destination = get_paths()
    destination_exists = validate_paths(source, destination)
    
    success = sync_plugin(source, destination, force=args.force, dry_run=args.dry_run)
    sys.exit(0 if success else 1)


if __name__ == "__main__":
    main()
