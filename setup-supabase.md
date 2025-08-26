# Supabase Setup Guide for Jani Pakwan Center

## Prerequisites
1. Create a Supabase account at [supabase.com](https://supabase.com)
2. Create a new project in Supabase

## Step 1: Database Setup

1. **Get your Supabase credentials:**
   - Go to your Supabase Dashboard
   - Navigate to Settings > Database
   - Copy the connection details
   - Navigate to Settings > API
   - Copy the Project URL and API keys

2. **Run the migration:**
   - In your Supabase Dashboard, go to SQL Editor
   - Copy the contents of `supabase/migrations/create_jani_pakwan_schema.sql`
   - Paste and run the SQL script

## Step 2: Environment Configuration

1. **Create environment file:**
   ```bash
   cp .env.example .env
   ```

2. **Update .env with your Supabase credentials:**
   ```env
   # Database Connection
   SUPABASE_DB_HOST=db.your-project-ref.supabase.co
   SUPABASE_DB_PORT=5432
   SUPABASE_DB_NAME=postgres
   SUPABASE_DB_USER=postgres
   SUPABASE_DB_PASSWORD=your-database-password

   # Supabase API
   SUPABASE_URL=https://your-project-ref.supabase.co
   SUPABASE_ANON_KEY=your-anon-key
   SUPABASE_SERVICE_ROLE_KEY=your-service-role-key
   ```

## Step 3: Authentication Setup

1. **Enable Email Authentication:**
   - Go to Authentication > Settings in Supabase Dashboard
   - Enable Email authentication
   - Disable email confirmation for development (optional)

2. **Deploy Edge Function (Optional):**
   - Install Supabase CLI
   - Run: `supabase functions deploy auth-helper`

## Step 4: Row Level Security

The migration script automatically sets up RLS policies:
- **Admin users** can manage expenses and users
- **All authenticated users** can manage orders, customers, and payments
- **Users can only see their own profile data**

## Step 5: Testing the Connection

1. **Test database connection:**
   - Access your application
   - Try logging in with default credentials:
     - Username: `Admin` / Password: `Admin@123`
     - Username: `user` / Password: `user@123`

2. **Verify functionality:**
   - Create a new order
   - Add expenses (admin only)
   - Check customer history
   - Test payment processing

## Step 6: Production Deployment

1. **Update RLS policies** if needed for your specific requirements
2. **Set up proper backup strategy** in Supabase
3. **Configure monitoring** and alerts
4. **Update default passwords** for security

## Key Features Enabled

✅ **Order Management** - Complete order lifecycle tracking
✅ **Customer Relationship** - Customer history and contact management  
✅ **Financial Tracking** - Income, expenses, and payment management
✅ **Supply Chain** - Party management and payment tracking
✅ **Multi-user Support** - Role-based access control
✅ **Real-time Updates** - Supabase real-time subscriptions ready
✅ **Scalable Architecture** - PostgreSQL with proper indexing
✅ **Security** - Row Level Security and authentication

## Troubleshooting

**Connection Issues:**
- Verify your database credentials
- Check if your IP is whitelisted in Supabase
- Ensure SSL is enabled

**Authentication Issues:**
- Check if users table is properly populated
- Verify RLS policies are not too restrictive
- Test with Supabase auth directly

**Performance Issues:**
- Check if indexes are created properly
- Monitor query performance in Supabase Dashboard
- Consider adding more specific indexes for your queries

## Next Steps

1. **Customize the schema** based on your specific business needs
2. **Add real-time subscriptions** for live updates
3. **Implement file storage** using Supabase Storage for receipts/documents
4. **Set up automated backups** and monitoring
5. **Add API rate limiting** and additional security measures

For support, check the Supabase documentation or contact your development team.