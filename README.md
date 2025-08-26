# Jani Pakwan Center - Supabase Edition

A comprehensive restaurant management system built with PHP and Supabase, featuring order management, customer tracking, financial management, and supply chain operations.

## 🚀 Features

- **Order Management**: Complete order lifecycle from creation to fulfillment
- **Customer Relationship Management**: Track customer history and preferences
- **Financial Tracking**: Monitor income, expenses, and payments
- **Supply Chain Management**: Manage suppliers and track payments
- **Multi-user Support**: Role-based access control (Admin/User)
- **Real-time Dashboard**: Live updates on orders, sales, and expenses
- **Print System**: Professional order receipts with Urdu support
- **Responsive Design**: Works on desktop, tablet, and mobile devices

## 🛠 Technology Stack

- **Backend**: PHP 8.0+
- **Database**: PostgreSQL (Supabase)
- **Frontend**: HTML5, CSS3, JavaScript (jQuery)
- **UI Framework**: Bootstrap 5
- **Authentication**: Supabase Auth
- **Real-time**: Supabase Realtime (ready to implement)

## 📋 Prerequisites

- PHP 8.0 or higher
- Web server (Apache/Nginx)
- Supabase account
- Modern web browser

## 🔧 Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd jani-pakwan-center
```

### 2. Set up Supabase

1. Create a new project at [supabase.com](https://supabase.com)
2. Go to SQL Editor in your Supabase dashboard
3. Run the migration script from `supabase/migrations/create_jani_pakwan_schema.sql`

### 3. Configure Environment

1. Copy the environment template:
```bash
cp .env.example .env
```

2. Update `.env` with your Supabase credentials:
```env
SUPABASE_DB_HOST=db.your-project-ref.supabase.co
SUPABASE_DB_PASSWORD=your-database-password
SUPABASE_URL=https://your-project-ref.supabase.co
SUPABASE_ANON_KEY=your-anon-key
```

### 4. Deploy to Web Server

Upload all files to your web server directory and ensure proper permissions.

## 🔐 Default Login Credentials

- **Admin**: Username: `Admin`, Password: `Admin@123`
- **User**: Username: `user`, Password: `user@123`

**⚠️ Important**: Change these default passwords immediately after setup!

## 📊 Database Schema

The system includes the following main tables:

- `customers` - Customer information and contact details
- `orders` - Order management with delivery tracking
- `order_items` - Individual items within orders
- `payments` - Payment tracking and history
- `expenses` - Business expense management
- `users` - User authentication and roles
- `parties` - Supply party management
- `party_payments` - Supplier payment tracking
- `khata` - Account ledger system

## 🔒 Security Features

- **Row Level Security (RLS)**: Database-level access control
- **Role-based Access**: Admin and User roles with different permissions
- **Input Validation**: Server-side validation for all inputs
- **SQL Injection Protection**: Prepared statements throughout
- **Session Management**: Secure session handling
- **Password Hashing**: Bcrypt password encryption

## 📱 Key Modules

### Order Management
- Create new orders with multiple items
- Track order status (Pending, Partially Paid, Fulfilled)
- Manage delivery dates and addresses
- Print professional receipts

### Customer Management
- Customer history and order tracking
- Search by name or phone number
- Payment history and outstanding balances

### Financial Management
- Income tracking from orders and payments
- Expense management with categories
- Dashboard with real-time financial metrics

### Supply Chain
- Manage supply parties and vendors
- Track payments to suppliers
- Khata (ledger) system for account management

## 🎨 UI/UX Features

- **Responsive Design**: Mobile-first approach
- **Modern Interface**: Clean, professional design
- **Real-time Updates**: Live dashboard metrics
- **Print Support**: Professional receipt printing
- **Multi-language**: English and Urdu support
- **Accessibility**: WCAG compliant design

## 🔧 Configuration Options

### Database Settings
- Connection pooling
- Query optimization
- Automatic backups via Supabase

### Application Settings
- Timezone configuration
- Currency formatting
- Date formats
- File upload limits

### Security Settings
- Session timeout
- Password requirements
- Login attempt limits
- API rate limiting

## 📈 Performance Optimization

- **Database Indexing**: Optimized queries with proper indexes
- **Caching**: Strategic caching for frequently accessed data
- **Lazy Loading**: Efficient data loading patterns
- **Minified Assets**: Compressed CSS and JavaScript
- **CDN Integration**: External library loading

## 🚀 Deployment

### Production Checklist

- [ ] Update default passwords
- [ ] Configure SSL certificates
- [ ] Set up automated backups
- [ ] Configure monitoring and alerts
- [ ] Update RLS policies if needed
- [ ] Test all functionality
- [ ] Set up error logging
- [ ] Configure email notifications

### Hosting Recommendations

- **Shared Hosting**: Works with most PHP hosting providers
- **VPS**: Recommended for better performance
- **Cloud Platforms**: Heroku, DigitalOcean, AWS
- **Database**: Supabase (included)

## 🔍 Troubleshooting

### Common Issues

**Database Connection Errors**
- Verify Supabase credentials in `.env`
- Check IP whitelist in Supabase dashboard
- Ensure SSL is enabled

**Authentication Issues**
- Verify users table is populated
- Check RLS policies
- Test with direct Supabase auth

**Performance Issues**
- Monitor query performance in Supabase
- Check database indexes
- Review RLS policy complexity

## 📚 API Documentation

### Authentication Endpoints
- `POST /auth/login.php` - User login
- `POST /auth/logout.php` - User logout

### Order Management
- `POST /api/create_order.php` - Create new order
- `GET /api/get_order.php` - Get order details
- `GET /api/customer_history.php` - Customer order history

### Financial Management
- `GET /api/dashboard.php` - Dashboard metrics
- `POST /api/expenses.php` - Add expense
- `GET /api/income.php` - Income reports

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

For support and questions:
- Check the troubleshooting section
- Review Supabase documentation
- Contact the development team

## 🔄 Version History

- **v2.0.0** - Supabase integration, enhanced security
- **v1.0.0** - Initial release with MySQL

---

**Made with ❤️ for Jani Pakwan Center**